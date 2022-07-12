<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Webmon</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.1.0/highcharts.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.1/themes/grid.js"></script>
    <script>
        var chart;

        function requestDatta(interface) {
            $.ajax({
                url: 'airmax.php?$result',
                datatype: "json",
                success: function(data) {
                    var midata = JSON.parse(data);
                    if (midata.length > 0) {
                        var TX = parseFloat(midata[0].data);
                        var RX = parseFloat(midata[1].data);
                        var x = (new Date()).getTime();
                        shift = chart.series[0].data.length > 19;
                        chart.series[0].addPoint([x, TX], true, shift);
                        chart.series[1].addPoint([x, RX], true, shift);
                        document.getElementById("traffic").innerHTML = TX + " / " + RX;
                    } else {
                        document.getElementById("traffic").innerHTML = "- / -";
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.error("Status: " + textStatus + " request: " + XMLHttpRequest);
                    console.error("Error: " + errorThrown);
                }
            });
        }
        $(document).ready(function() {
            Highcharts.setOptions({
                global: {
                    useUTC: false
                }
            });

            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'container',
                    animation: Highcharts.svg,
                    type: 'line', //line,//area,//spline


                    events: {
                        load: function() {
                            setInterval(function() {
                                requestDatta(document.getElementById("interface").value);
                            }, 1000);
                        }
                    }
                },


                title: {
                    text: '<p style="font-size: 25px; color: red">INTERNET </p>'
                },
                xAxis: {
                    type: 'datetime',
                    tickPixelInterval: 150,
                    maxZoom: 30 * 1000
                },
                yAxis: {
                    minPadding: 0.2,
                    maxPadding: 0.2,
                    allowDecimals: true,
                    title: {
                        text: 'Traffic',
                        margin: 80
                    }
                },
                series: [{
                    name: '<p style="font-size: 20px; color: blue">Upload Mbps</p>',
                    data: []
                }, {
                    name: '<p style="font-size: 20px; color: green">Download Mbps</p>',
                    data: []
                }]
            });
        });
    </script>
</head>

<body>
    <div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
    <input type=hidden name="interface" id="interface" type="text" />
    <div align="center" style="font-size:200%" id="traffic"></div>
</body>

</html>
