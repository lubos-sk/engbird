
<?php

// zaciatok progrmau
$start_time = microtime(TRUE);

//pomocna funkcia na prepocet kb/mb pre memory usage
function convert($size)
{
    $unit=array('B','kB','MB','GB','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

echo "
<html>
    <head><meta charset=\"utf-8\" /></head>
    <body>
        
        <script type=\"text/javascript\" src=\"./plotly/plotly.min.js\"></script>
        <script src=\"./plotly/plotly-locale-sk-sk.js\"></script>
        <script>Plotly.setPlotConfig({locale: 'sk-sk'})</script>
            
        <script type=\"text/javascript\" src=\"figure.js\"></script>
        
        <div id=\"d9adc420-b7bd-4cb9-a188-4f3240941e34\" style=\"width: 1152px; height: 686px;\" class=\"plotly-graph-div\"></div>
        <script type=\"text/javascript\">
            (function(){
                window.PLOTLYENV={'BASE_URL': 'https://plot.ly'};

                var gd = document.getElementById('d9adc420-b7bd-4cb9-a188-4f3240941e34')
                var resizeDebounce = null;

                function resizePlot() {
                    var bb = gd.getBoundingClientRect();
                    Plotly.relayout(gd, {
                        width: bb.width,
                        height: bb.height
                    });
                }

                
                window.addEventListener('resize', function() {
                    if (resizeDebounce) {
                        window.clearTimeout(resizeDebounce);
                    }
                    resizeDebounce = window.setTimeout(resizePlot, 100);
                });
                

                
                Plotly.plot(gd,  {
                    data: figure.data,
                    layout: figure.layout,
                    frames: figure.frames,
                    config: {\"showLink\": true, \"linkText\": \"Export to plot.ly\", \"mapboxAccessToken\": \"pk.eyJ1IjoiY2hyaWRkeXAiLCJhIjoiY2lxMnVvdm5iMDA4dnhsbTQ5aHJzcGs0MyJ9.X9o_rzNLNesDxdra4neC_A\"}
                });
                
           }());
        </script>
    </body>
</html>
";

$memory_usage = convert(memory_get_usage(true));
$memory_peak = convert(memory_get_peak_usage(true));
$end_time = microtime(TRUE);
$time_taken = $end_time - $start_time;
$time_taken = round($time_taken,5);
echo '<center>stránka vygenerovaná za '.$time_taken.' sekúnd RAM: '.$memory_usage.' - '.$memory_peak.'</center>';
?>