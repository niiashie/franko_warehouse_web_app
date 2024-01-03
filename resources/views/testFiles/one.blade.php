<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    @foreach ($result as $row)
        <h1>{{ $row->name }}</h1>
        <h2>{{ $row->category->name}}</h2>
    @endforeach
</body>
</html>