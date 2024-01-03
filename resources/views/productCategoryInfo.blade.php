<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href={{ asset('css/productCategory.css') }} />
    <script type="text/javascript" src="../js/jquery.js"></script> 
    <script type="text/javascript" src="../js/setup.js"></script> 
    <title>{{ $category }} Products</title>
</head>
<script>
    var totolAccraQuantity = 0,totalKumasiQuantity = 0,totalAccraValue=0,totalKumasiValue=0;
</script>
<body>
    <div id="bottomMenu"></div>
    <div id="mainCenterContainer">
       <img id="closeIcon" onclick="window.history.go(-1); return false;" src={{ asset('icons/big_close.png') }}>
       <div id="categoryContainer">
         <label id="categoryTxt">{{ $category }} Products</label>
       </div>
       @foreach ($data as $row => $obj)
          <div class="warehouseNameCon">
             <label class="warehouseNameTxt">{{ $row }}</label>
          </div>
          <table class="categoryContentsTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Value</th>
                </tr> 
            </thead>
            <tbody>
                @if(!$data[$row]["0"]->isEmpty())
                      
                    @foreach ($data[$row]["0"][$category] as $detail)
                        <tr>
                            <td>{{ $detail->name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td> &#8373 {{ $detail->value }}</td>
                        </tr>
                    @endforeach
                        <tr>
                            <td>Total</td>
                            <td>{{ $data[$row]['total_quantity'] }}</td>
                            <td> &#8373 {{ $data[$row]['total_value'] }}</td>
                        </tr>
                    
                @endif
            </tbody>
          </table>
       @endforeach
    </div>
</body>
</html>