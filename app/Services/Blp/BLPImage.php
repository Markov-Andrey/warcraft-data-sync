<?php

namespace App\Services\Blp;

/*
    Created by TriggerHappy
*/

const MAGIC_BLP_V0          = "BLP0";
const MAGIC_BLP_V1          = "BLP1";
const MAGIC_BLP_V2          = "BLP2";

const BLP_COMPRESSION_JPEG  = 0;
const BLP_COMPRESSION_NONE  = 1;
const BLP_JPEG_HEADER_SIZE  = 624;

class BLPImage
{
    private $filename, $file, $filesize, $stream;
    private $compression, $flags, $width, $height, $type, $alphaBits;
    private $mipmapOffset, $mipmapSize, $hasMipmaps;
    private $image, $imageData;

    /**
     * @throws \ImagickException
     * @throws \Exception
     */
    function __construct($path)
    {
        if (!file_exists($path))
        {
            throw new \Exception('File doesn\'t exist.');
        }

        $this->filename = $path;
        $this->filesize = filesize($path);
        $this->file = fopen($path, 'rb');
        $this->stream = new BLPReader($this->file);

        if (!$this->parse())
        {
            throw new \Exception('Invalid image header.');
        }
    }

    function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->file && get_resource_type($this->file) == 'stream') fclose($this->file);
        if ($this->image) $this->image->clear();
    }

    public function image(){ return $this->image; }
    public function width(){ return $this->width; }
    public function height(){ return $this->width; }
    public function hasMipmaps(){ return $this->hasMipmaps; }
    public function filename(){ return $this->filename; }
    public function filesize(){ return $this->filesize; }

    public function saveAs($filename, $filetype=null)
    {
        if ($filetype == null)
        {
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        }

        $this->image->setImageFormat($filetype);
        $this->image->writeImage($filename);
    }


    /**
     * @throws \ImagickException
     */
    private function parse()
    {
        $this->stream->setPosition(0);
        $valid_header = false;

        while (!$valid_header && $this->stream->fp < $this->filesize) {
            $buffer = $this->stream->readBytes(4);

            if ($buffer == MAGIC_BLP_V2) throw new \Exception("BLP2 files are not supported.");
            if ($buffer != MAGIC_BLP_V0 && $buffer != MAGIC_BLP_V1) continue;

            // parse header
            [$this->compression, $this->alphaBits, $this->width, $this->height, $this->type, $this->hasMipmaps] =
                array_map(fn() => $this->stream->readUInt32(), range(0,5));

            if (!in_array($this->alphaBits, [0,1,4,8])) {
                trigger_error("BLPImage: alphaBits is $this->alphaBits, defaulting to 0.", E_USER_WARNING);
                $this->alphaBits = 0;
            }

            if ($buffer == MAGIC_BLP_V1) {
                $this->mipmapOffset = array_map(fn() => $this->stream->readUInt32(), range(0,15));
                $this->mipmapSize   = array_map(fn() => $this->stream->readUInt32(), range(0,15));
            } else {
                $info = pathinfo($this->filename);
                $fname = "{$info['dirname']}/".basename($info['basename'],'.'.$info['extension']).".b00";
                if (!file_exists($fname)) throw new \Exception("BLP0 image is missing a mipmap file.");
                $this->imageData = file_get_contents($fname);
            }

            if ($this->compression === BLP_COMPRESSION_NONE) {
                $this->parsePaletted();
            } else {
                $jpeg_header_size = $this->stream->readUInt32();
                if ($jpeg_header_size > BLP_JPEG_HEADER_SIZE || $jpeg_header_size > ($this->filesize - $this->stream->fp))
                    trigger_error("BLPImage: Unsafe header size ($jpeg_header_size).", E_USER_WARNING);
                if (!in_array($this->alphaBits,[0,8])) trigger_error("BLPImage: alphaBits is $this->alphaBits (expected 0 or 8)", E_USER_WARNING);

                $jpeg_header = $this->stream->readBytes($jpeg_header_size);
                if ($buffer == MAGIC_BLP_V1) {
                    $this->stream->setPosition($this->mipmapOffset[0]);
                    $this->imageData = $this->stream->readBytes($this->mipmapSize[0]);
                }

                $this->image = new \Imagick();
                $this->image->readImageBlob($jpeg_header . $this->imageData);
                $this->image->setColorspace(\Imagick::COLORSPACE_SRGB);
                $this->rebuildWithoutAlpha();
                $this->image = BLPImage::BGR2RGB($this->image);
            }

            $valid_header = true;
        }

        return $valid_header;
    }

    /**
     * @throws \ImagickException
     */
    private function parsePaletted(): void
    {
        $rgb = [];
        $im = imagecreatetruecolor($this->width,$this->height);
        imagealphablending($im,false);
        imagesavealpha($im,true);

        for($i=0;$i<256;$i++){
            [$b,$g,$r,$a] = array_map(fn()=> $this->stream->readInt(), range(0,3));
            $rgb[] = [$r,$g,$b];
        }

        $this->stream->setPosition($this->mipmapOffset[0]);
        $size = $this->width*$this->height;
        $index_list = array_map(fn()=> $this->stream->readInt(), range(0,$size-1));
        $alpha_list = $this->alphaBits>0 ? array_map(fn()=> $this->stream->readInt(), range(0,$size-1)) : [];

        for($y=0,$color_index=0;$y<$this->height;$y++){
            for($x=0;$x<$this->width;$x++,$color_index++){
                $value = $rgb[$index_list[$color_index]];
                $alpha = match($this->alphaBits){
                    8 => $alpha_list[$color_index],
                    4 => $color_index%2 ? $alpha_list[$color_index]>>4 : $alpha_list[$color_index]&0xF,
                    1 => $alpha_list[(int)($color_index/8)] & (1<<($color_index%8)),
                    default => 255
                };
                $color = imagecolorallocatealpha($im,$value[0],$value[1],$value[2],127-(127*($alpha/255)));
                imagesetpixel($im,$x,$y,$color);
            }
        }

        ob_start();
        imagepng($im);
        $this->image = new \Imagick();
        $this->image->readImageBlob(ob_get_clean());
        imagedestroy($im);
    }

    /**
     * @throws \ImagickException
     */
    private function rebuildWithoutAlpha(): void
    {
        $this->image = (function($img, $w, $h) {
            $out = new \Imagick();
            $out->newImage($w, $h, new \ImagickPixel('transparent'));
            foreach (['blue'=>\Imagick::COMPOSITE_COPYBLUE, 'green'=>\Imagick::COMPOSITE_COPYGREEN, 'red'=>\Imagick::COMPOSITE_COPYRED] as $color => $comp) {
                $tmp = clone $img;
                $tmp->separateImageChannel(constant("Imagick::CHANNEL_" . strtoupper($color)));
                $out->compositeImage($tmp, $comp, 0, 0);
                $tmp->clear();
            }
            $out->negateImage(false);
            return $out;
        })($this->image, $this->width, $this->height);
    }

    private static function BGR2RGB($image)
    {
        $image->colorMatrixImage([
            0, 0, 1, 0, 0,
            0, 1, 0, 0, 0,
            1, 0, 0, 0, 0,
            0, 0, 0, 1, 0,
            0, 0, 0, 0, 1,
        ]);

        return $image;
    }

}
