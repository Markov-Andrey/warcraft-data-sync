<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W3x Configuration</title>
</head>
<body>
<h1>W3x Configuration</h1>

<h2>Directories</h2>
<table border="1">
    <thead>
    <tr>
        <th>Directory</th>
        <th>Copy</th>
    </tr>
    </thead>
    <tbody>
    @foreach($directories as $directoryName => $directoryData)
        <tr>
            <td>{{ $directoryName }}</td>
            <td>{{ $directoryData['copy'] ? '+' : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Files</h2>
<table border="1">
    <thead>
    <tr>
        <th>File</th>
        <th>Copy</th>
    </tr>
    </thead>
    <tbody>
    @foreach($files as $fileName => $fileData)
        <tr>
            <td>{{ $fileName }}</td>
            <td>{{ $fileData['copy'] ? '+' : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
