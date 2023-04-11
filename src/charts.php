<?php
require '../vendor/autoload.php';

use \OpenAI as OpenAI;
use \GuzzleHttp\Client;

$chave = file_get_contents('../key.txt');

$client = OpenAI::client($chave);

if (isset($_GET['input'])) {
    $text = json_decode($_GET['input']);
    $stream = $client->chat()->create([
        'model' => 'gpt-4',
        'messages' => [
            [
                'role' => 'system', 'content' => <<<TEXT
                Por favor, forneça um texto para que eu possa identificar as palavras mais relevantes. Responderei com as chaves 'labels' e 'data', onde as etiquetas correspondem às palavras mais relevantes encontradas e os dados correspondem às porcentagens dessas palavras em relação ao total de palavras do texto. Por favor, certifique-se de que o texto tenha pelo menos 100 palavras. Depois de receber as etiquetas e dados, forneça a média dos valores em uma mensagem de resposta.

                Responda com o seguinte formato:

                {
                  "resumo": "resumo do texto fornecido",
                  "labels": [label1, label2, label3, ...],
                  "data": [data1, data2, data3, ...],
                  "color": "rgba(###, ###, ###, 0.2)"
                }

TEXT
            ],
            ['role' => 'user', 'content' => $text],
        ],
    ]);
    $response = $stream->choices[0]->toArray();
    echo $response['message']['content'];
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts</title>
    <style>
        body {
            background-color: #f1f1f1;
        }

        #myChart canvas {
            max-height: 300px !important;
            width: 100%;
        }

        textarea {
            width: 100%;
            height: 200px;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
            resize: none;
        }
    </style>
</head>

<body>
    <div>
        <textarea id="text">
        O turismo é uma das principais indústrias em muitos países do mundo. Milhões de pessoas viajam para diferentes partes do mundo a cada ano para explorar novos lugares, experimentar novas culturas e aprender sobre a história do mundo. Algumas das principais atrações turísticas incluem monumentos históricos, museus, parques nacionais e praias. Além disso, muitos países têm programas de incentivo ao turismo para atrair visitantes estrangeiros e impulsionar suas economias.

        No entanto, o turismo também pode ter um impacto negativo no meio ambiente e nas comunidades locais. A construção de grandes hotéis e resorts pode danificar ecossistemas frágeis e deslocar comunidades locais. Além disso, o turismo em massa pode levar a problemas como a superlotação e a degradação da qualidade de vida para os moradores locais. Portanto, é importante equilibrar o turismo com a proteção ambiental e o bem-estar das comunidades locais.
    </textarea>
        <button id="submit">Enviar</button>
        <!-- Loading -->
        <span id="loading" style="display: none;">Loading...</span>
    </div>
    <!-- <canvas id="myChart" style="display: none;"></canvas> -->
    <div id="myChart" style="display: none;">
    </div>
    <p id="resumo" style="display: none;"></p>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Remove qualquer canvas existente
            $.fn.renderChart = function(labels, data, color) {
                $("canvas").remove();
                const ctx = document.createElement('canvas').getContext('2d');
                $("#myChart").append(ctx.canvas);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Palavras relevantes',
                            data: data,
                            borderWidth: 1,
                            backgroundColor: color
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                // Atualizar o gráfico depois que receber a resposta

            }

            $("#submit").on('click', function() {
                const text = $("#text").val();
                $("#loading").show();
                $.get('#', {
                    input: JSON.stringify(text)
                }, function(response) {
                    response = JSON.parse(response);
                    const labels = response.labels;
                    const data = response.data;
                    const summary = response.resumo;
                    const color = response.color;
                    console.log(labels, data, color);
                    $.fn.renderChart(labels, data, color);
                    $("#loading").hide();
                    $("#myChart").show();
                    $("#resumo").show().text(summary);
                });
            });
        });
    </script>
</body>

</html>