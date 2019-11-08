<?php
    header("Access-Control-Allow-Origin: *");
    
    if(isset($_GET['coin'])){
      $coin = $_GET['coin'];
    }else{
      $coin = "BTCUSDT";
    }

    if(isset($_GET['periode']) && isset($_GET['reload'])){
      $periode = $_GET['periode'];
      $reload = $_GET['reload'];
    }else{
      $periode = "30min";
      $reload = "3600";
    }

    function getJSON(){
      global $coin;
      global $periode;
      $url = "https://api.coinex.com/v1/market/kline?market=".$coin."&type=".$periode;
      $data = getData($url);
      return $data;    
    }

    function getPair(){
      $url = "https://api.coinex.com/v1/market/list";
      $data = getData($url);
      $data = json_decode($data, true);
      $json = array();
      foreach ($data['data'] as $key => $value) {
        $json[] = array(
                'label' => $value,
                'value'=> $value,
              );
      }
      return json_encode($json);
    }

    function getData($url){    
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        $result = curl_exec($ch); 
        curl_close($ch);
        return $result;
    }   
?>

<!-- Styles -->
<style>
#chartdiv {
  width: 100%;
  height: 95%;
}
.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
    /* add padding to account for vertical scrollbar */
    padding-right: 20px;
} 
</style>

<!-- Resources -->
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/amstock.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script type="text/javascript" src="http://www.amcharts.com/lib/3/exporting/amexport_combined.js"></script>
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<!-- JQUERY PLUGIN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>

<!-- BOOTSTRAP PLUGIN -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

<!-- Chart code -->
<script>
var cross = [];
var chartData = <?php echo getJSON(); ?>;
chartData = processData(chartData.data, false);
cross = processData(chartData, true);
console.log(cross);
var title = "Coinex Candle Paircoin : <?php echo $coin.' - Periode : '.$periode.' - Refresh : '.$reload.' Detik'; ?>";
var pair = <?php echo getPair(); ?>;
var reloadTime = <?php echo $reload; ?>*1000;

function processData(Lists, getCross){
  var res = [];
  list = Lists.slice(50,(Lists.length));
  var persenK = [];
  var tgl = '';
  var mae = 0;
  var avgGain = 0;
  var avgLoss = 0;
  for(var i = 0; i < list.length; i++) {
    //Simpan data candle: Close, Open, High dan Low kedalam variabel dp
    var dp = {};
  	var arrow = {};
    dp["close"] = list[i][2];
    dp["open"] = list[i][1];
    dp["high"] = list[i][3];
    dp["low"] = list[i][4];
    tgl = new Date((list[i][0])*1000).toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });
    dp["date"] = tgl;
    dp["volume"] = list[i][5];

    //Hitung Moving Average dari data indeks ke-0, dan simpan ke variabel dp pada indeks 
    var sum = 0;
    for(var j = i; j<(i+50); j++){
      sum = sum+parseFloat(Lists[j][4]);
    }sum = sum/50;
    dp["ma"]=sum.toFixed(8);

    //Hitung Moving Average Eksponensial dan simpan ke variabel dp pada indeks mae
    var close = parseFloat(list[i][4]);
    if(i==0){
      mae = ((close-sum)*(2/6))+sum;
    }else{
      mae = ((close-mae)*(2/6))+mae;
    }
    dp["mae"] = mae.toFixed(8);

    //Hitung Cross
    if(i>1){
    	if(res[i-1].ma < res[i-1].mae){
    		if(res[i-2].ma > res[i-2].mae){
    			arrow["date"] = new Date((list[i-2][0])*1000);
    			arrow["type"] = "arrowUp";
    			arrow["backgroundColor"] = "#00CC00";
    			arrow["graph"] = "MAE";
    			arrow["description"] = "Harga mau naik, Ayo Beli !!!";
    			cross.push(arrow);
    			}
    	}else if(res[i-1].ma > res[i-1].mae){
    		if(res[i-2].ma < res[i-2].mae){
    			arrow["date"] = new Date((list[i-2][0])*1000);
    			arrow["type"] = "arrowDown";
    			arrow["backgroundColor"] = "#CC0000";
    			arrow["graph"] = "MAE";
    			arrow["description"] = "Harga mau turun, Ayo Jual !!!";
    			cross.push(arrow);
    		}
    	}
    }
    
    //Masukkan variabel dp kedalam array res.
    res.push(dp);
  }
  if(getCross){
  	return cross;
  }else{
  	return res;
  }
}

var chart = AmCharts.makeChart( "chartdiv", {
  "type": "stock",
  "theme": "light",
  "categoryAxesSettings": {
    "minPeriod": "mm",
    "groupToPeriods": [ '30mm', '30mm' ]
  },
  "valueAxesSettings": {
    "inside": false
  },
  "chartCursorSettings": {
    "fullWidth": true,
    "cursorAlpha": 0.5,
    "pan": true,
    "valueBalloonsEnabled": true,
    "valueLineEnabled": true,
    "valueLineBalloonEnabled": true,
    "valueLineAlpha": 1
  },
  "dataSets": [ {
    "fieldMappings": [ {
      "fromField": "open",
      "toField": "open"
    }, {
      "fromField": "close",
      "toField": "close"
    }, {
      "fromField": "high",
      "toField": "high"
    }, {
      "fromField": "low",
      "toField": "low"
    }, {
      "fromField": "volume",
      "toField": "volume"
    }, {
      "fromField": "ma",
      "toField": "ma"
    }, {
      "fromField": "mae",
      "toField": "mae"
    }, {
      "fromField": "date",
      "toField": "date"
    } ],
    "color": "#7f8da9",
    "dataProvider": chartData,
    "categoryField": "date",
    "stockEvents": cross,
  } ],
  "panels": [ {
    "title": title,
    "percentHeight": 100,
    "autoMargins": true,
    "showCategoryAxis": false,
    "stockGraphs": [ {
      "id": "g1",
      "title": "Close",
      "type": "candlestick",
      "openField": "open",
      "closeField": "close",
      "highField": "high",
      "lowField": "low",
      "valueField": "close",
      "lineColor": "#7f8da9",
      "fillColors": "#7f8da9",
      "negativeLineColor": "#db4c3c",
      "negativeFillColors": "#db4c3c",
      "fillAlphas": 1,
      "balloonText": "Open:<b>[[open]]</b><br>Close:<b>[[close]]</b><br>Low:<b>[[low]]</b><br>High:<b>[[high]]</b>",
      "useDataSetColors": false
    }, {
      "id": "MA",
      "title": "MA 50 - (Moving Average)",
      "lineAlpha": 1,
      "lineThickness": 2,
      "lineColor": "#f00",
      "type": "smoothedLine",
      "valueField": "ma",
      "useDataSetColors": false
    }, {
      "id": "MAE",
      "title": "MAE 5 - (Moving Average Eksponensial)",
      "lineAlpha": 1,
      "lineThickness": 2,
      "lineColor": "#00f",
      "type": "smoothedLine",
      "valueField": "mae",
      "useDataSetColors": false
    }],
    "stockLegend": {
      "position": "bottom",
      "maxColumns": 3,
      "spacing": 50,
      "useGraphSettings": true
    }    
  },{
    "title": "Volume",
    "percentHeight": 20,
    "stockGraphs": [{
        "id": "vol",
        "title": "Volume",
        "lineAlpha": 1,
        "lineThickness": 3,
        "lineColor": "#137a7f",
        "type": "smoothedLine",
        "valueField": "volume",
        "useDataSetColors": false
    }]
  }],
  "panelsSettings": {
    "marginLeft": 30,
    "marginRight": 10
  },
  "chartScrollbarSettings": {
    "graph": "g1",
    "graphType": "line",
    "usePeriod": "mm",
    "updateOnReleaseOnly": false,
    "height": 30
  },
    "periodSelector": {
    "position": "bottom",
    "inputFieldsEnabled": false,
    "dateFormat": "JJ:NN",
    "inputFieldWidth": 150,
    "periods": [ {
      "period": "DD",
      "count": 1,
      "label": "1 Day"
    },{
      "period": "DD",
      "count": 3,
      "label": "3 Day",
      "selected": true
    }, {
      "period": "MAX",
      "label": "MAX"
    }]
  },
  "export": {
    "enabled": true,
    "position": "top-right"
  }
} );

chart.addListener("zoomed",function(e) {
	console.log(cross);
    setTimeout(function() {
        var ame = new AmCharts.AmExport(e.chart,{},true);
        ame.output({
            format: "png",
            output: "datastring"
        },function(datastring) {
            $.ajax({
              url: "http://11api-botlist.000webhostapp.com/candle_coinex/img.php",
              type: "POST",
              data: {
                     imgdata:datastring
                   }
            });
        });
    },1000); // startDuration
});

setInterval(function(){
  location.reload();
}, reloadTime);
</script>

<!-- HTML -->
<div class="container">
  <form action="candle.php" method="GET" class="pb-0" style="margin: 0 auto;height: 4%;">
    <div class="row">
      <div class="form-group col-sm-2">
        <input class="form-control form-control-sm" type="text" id="coin" name="coin" placeholder="Pilih Coin / Pair" required>
      </div>
      <div class="form-group col-sm-3">
        <select class="form-control form-control-sm" id="periode" name="periode">
          <option value="30min" selected>Pilih Periode Candle</option>
          <option value="5min">5 Menit</option>
          <option value="15min">15 Menit</option>
          <option value="30min">30 Menit</option>
          <option value="1hour">1 Jam</option>
        </select>
      </div>
      <div class="form-group col-sm-3">
        <select class="form-control form-control-sm" id="reload" name="reload">
          <option value="30" selected>Pilih Waktu Reload</option>
          <option value="5">5 Detik</option>
          <option value="10">10 Detik</option>
          <option value="30">30 Detik</option>
          <option value="3600">1 Jam</option>
        </select>
      </div>
      <div class="form-group col-sm-2">
        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
      </div>
    </div>
  </form>
</div>
<hr style="margin: 4 auto;">
<div id="chartdiv" class="pt-0" style="margin: 0 auto;"></div>

<script>
  $(document).ready(function(){
    $('#coin').autocomplete({
      minLength: 1, //minimal huruf saat autocomplete di proses
      source: pair, //file untuk mencari data mahasiswa
      select:function(event, ui){
        $('#coin').val(ui.item.symbol);
      }
    })
  });
</script>