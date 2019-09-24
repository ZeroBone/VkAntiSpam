<?php

use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Панель управления
            </h1>
        </div>
        <div class="row row-cards">
            <?php

            $db = VkAntiSpam::get()->getDatabaseConnection();

            $query = $db->prepare('
SELECT COUNT(*) AS `result` FROM `messages` WHERE `date` > ?
UNION
SELECT COUNT(*) AS `result` FROM `bans` WHERE `date` > ?
UNION
SELECT COUNT(*) AS `result` FROM `trainingSet`
UNION
SELECT COUNT(*) AS `result` FROM `trainingSet` WHERE `category` = ?
UNION
SELECT COUNT(*) AS `result` FROM `wordFrequency`
UNION
SELECT COUNT(*) AS `result` FROM `users`;
');

            $query->execute([
                time() - 86400,
                time() - 86400,
                TextClassifier::CATEGORY_HAM
            ]);

            $messagesToday = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $bansToday = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $totalTrained = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $hamTrained = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $wordsCount = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $usersCount = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            ?>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($messagesToday, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Не-спам сообщений за последние сутки</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($bansToday, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Блокировок за последние сутки</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($totalTrained - $hamTrained, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Спама обучено</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($hamTrained, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Не-спама обучено</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($wordsCount, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Слов проанализировано</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($usersCount, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Пользователей</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cards">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Development Activity</h3>
                    </div>
                    <div id="chart-development-activity" style="height: 10rem; max-height: 160px; position: relative;" class="c3"><svg width="454" height="160" style="overflow: hidden;"><defs><clipPath id="c3-1569338568517-clip"><rect width="455" height="148"></rect></clipPath><clipPath id="c3-1569338568517-clip-xaxis"><rect x="-31" y="-20" width="517" height="28"></rect></clipPath><clipPath id="c3-1569338568517-clip-yaxis"><rect x="-29" y="-4" width="19" height="172"></rect></clipPath><clipPath id="c3-1569338568517-clip-grid"><rect width="455" height="148"></rect></clipPath><clipPath id="c3-1569338568517-clip-subchart"><rect width="455"></rect></clipPath></defs><g transform="translate(-0.5,4.5)"><text class="c3-text c3-empty" text-anchor="middle" dominant-baseline="middle" x="227.5" y="74" style="opacity: 0;"></text><rect class="c3-zoom-rect" width="455" height="148" style="opacity: 0;"></rect><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip)" class="c3-regions" style="visibility: visible;"></g><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-grid)" class="c3-grid" style="visibility: visible;"><g class="c3-xgrid-focus"><line class="c3-xgrid-focus" x1="437" x2="437" y1="0" y2="148" style="visibility: hidden;"></line></g></g><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip)" class="c3-chart"><g class="c3-event-rects c3-event-rects-single" style="fill-opacity: 0;"><rect class=" c3-event-rect c3-event-rect-0" x="-9.1" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-1" x="9.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-2" x="28.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-3" x="47.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-4" x="66.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-5" x="85.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-6" x="104.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-7" x="123.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-8" x="142.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-9" x="161.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-10" x="180.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-11" x="199.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-12" x="218.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-13" x="237.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-14" x="256.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-15" x="275.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-16" x="294.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-17" x="313.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-18" x="332.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-19" x="351.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-20" x="370.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-21" x="389.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-22" x="408.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-23" x="427.9" y="0" width="18.2" height="148"></rect><rect class=" c3-event-rect c3-event-rect-24" x="445.9" y="0" width="18.2" height="148"></rect></g><g class="c3-chart-bars"><g class="c3-chart-bar c3-target c3-target-data1" style="pointer-events: none;"><g class=" c3-shapes c3-shapes-data1 c3-bars c3-bars-data1" style="cursor: pointer;"></g></g></g><g class="c3-chart-lines"><g class="c3-chart-line c3-target c3-target-data1" style="opacity: 1; pointer-events: none;"><g class=" c3-shapes c3-shapes-data1 c3-lines c3-lines-data1"><path class=" c3-shape c3-shape c3-line c3-line-data1" d="M0,148L19,137.72027972027973L38,145.94405594405598L57,143.88811188811187L76,133.60839160839163L95,137.72027972027973L114,135.66433566433565L133,131.55244755244755L152,98.65734265734267L171,133.60839160839163L190,123.32867132867133L209,137.72027972027973L228,135.66433566433565L247,141.83216783216784L266,143.88811188811187L285,143.88811188811187L304,135.66433566433565L323,86.32167832167832L342,127.44055944055943L361,127.44055944055943L380,117.16083916083916L399,119.21678321678321L418,51.37062937062938L437,14.363636363636367L455,34.92307692307691" style="stroke: rgb(70, 127, 207); opacity: 1;"></path></g><g class=" c3-shapes c3-shapes-data1 c3-areas c3-areas-data1"><path class=" c3-shape c3-shape c3-area c3-area-data1" d="M0,148L19,137.72027972027973L38,145.94405594405598L57,143.88811188811187L76,133.60839160839163L95,137.72027972027973L114,135.66433566433565L133,131.55244755244755L152,98.65734265734267L171,133.60839160839163L190,123.32867132867133L209,137.72027972027973L228,135.66433566433565L247,141.83216783216784L266,143.88811188811187L285,143.88811188811187L304,135.66433566433565L323,86.32167832167832L342,127.44055944055943L361,127.44055944055943L380,117.16083916083916L399,119.21678321678321L418,51.37062937062938L437,14.363636363636367L455,34.92307692307691L455,148L437,148L418,148L399,148L380,148L361,148L342,148L323,148L304,148L285,148L266,148L247,148L228,148L209,148L190,148L171,148L152,148L133,148L114,148L95,148L76,148L57,148L38,148L19,148L0,148Z" style="fill: rgb(70, 127, 207); opacity: 0.1;"></path></g><g class=" c3-selected-circles c3-selected-circles-data1"></g><g class=" c3-shapes c3-shapes-data1 c3-circles c3-circles-data1" style="cursor: pointer;"><circle class=" c3-shape c3-shape-0 c3-circle c3-circle-0" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="0" cy="148"></circle><circle class=" c3-shape c3-shape-1 c3-circle c3-circle-1" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="19" cy="137.72027972027973"></circle><circle class=" c3-shape c3-shape-2 c3-circle c3-circle-2" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="38" cy="145.94405594405598"></circle><circle class=" c3-shape c3-shape-3 c3-circle c3-circle-3" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="57" cy="143.88811188811187"></circle><circle class=" c3-shape c3-shape-4 c3-circle c3-circle-4" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="76" cy="133.60839160839163"></circle><circle class=" c3-shape c3-shape-5 c3-circle c3-circle-5" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="95" cy="137.72027972027973"></circle><circle class=" c3-shape c3-shape-6 c3-circle c3-circle-6" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="114" cy="135.66433566433565"></circle><circle class=" c3-shape c3-shape-7 c3-circle c3-circle-7" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="133" cy="131.55244755244755"></circle><circle class=" c3-shape c3-shape-8 c3-circle c3-circle-8" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="152" cy="98.65734265734267"></circle><circle class=" c3-shape c3-shape-9 c3-circle c3-circle-9" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="171" cy="133.60839160839163"></circle><circle class=" c3-shape c3-shape-10 c3-circle c3-circle-10" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="190" cy="123.32867132867133"></circle><circle class=" c3-shape c3-shape-11 c3-circle c3-circle-11" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="209" cy="137.72027972027973"></circle><circle class=" c3-shape c3-shape-12 c3-circle c3-circle-12" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="228" cy="135.66433566433565"></circle><circle class=" c3-shape c3-shape-13 c3-circle c3-circle-13" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="247" cy="141.83216783216784"></circle><circle class=" c3-shape c3-shape-14 c3-circle c3-circle-14" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="266" cy="143.88811188811187"></circle><circle class=" c3-shape c3-shape-15 c3-circle c3-circle-15" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="285" cy="143.88811188811187"></circle><circle class=" c3-shape c3-shape-16 c3-circle c3-circle-16" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="304" cy="135.66433566433565"></circle><circle class=" c3-shape c3-shape-17 c3-circle c3-circle-17" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="323" cy="86.32167832167832"></circle><circle class=" c3-shape c3-shape-18 c3-circle c3-circle-18" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="342" cy="127.44055944055943"></circle><circle class="c3-shape c3-shape-19 c3-circle c3-circle-19" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="361" cy="127.44055944055943"></circle><circle class="c3-shape c3-shape-20 c3-circle c3-circle-20" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="380" cy="117.16083916083916"></circle><circle class="c3-shape c3-shape-21 c3-circle c3-circle-21" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="399" cy="119.21678321678321"></circle><circle class="c3-shape c3-shape-22 c3-circle c3-circle-22" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="418" cy="51.37062937062938"></circle><circle class="c3-shape c3-shape-23 c3-circle c3-circle-23" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="437" cy="14.363636363636367"></circle><circle class="c3-shape c3-shape-24 c3-circle c3-circle-24" r="2.5" style="fill: rgb(70, 127, 207); opacity: 0;" cx="455" cy="34.92307692307691"></circle></g></g></g><g class="c3-chart-arcs" transform="translate(227.5,69)"><text class="c3-chart-arcs-title" style="text-anchor: middle; opacity: 0;"></text></g><g class="c3-chart-texts"><g class="c3-chart-text c3-target c3-target-data1" style="opacity: 1; pointer-events: none;"><g class=" c3-texts c3-texts-data1"></g></g></g></g><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-grid)" class="c3-grid c3-grid-lines"><g class="c3-xgrid-lines"></g><g class="c3-ygrid-lines"></g></g><g class="c3-axis c3-axis-x" clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-xaxis)" transform="translate(0,148)" style="visibility: hidden; opacity: 1;"><text class="c3-axis-x-label" transform="" style="text-anchor: end;" x="455" dx="-0.5em" dy="-0.5em"></text><g class="tick" transform="translate(0, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="0"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">0</tspan></text></g><g class="tick" transform="translate(19, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">1</tspan></text></g><g class="tick" transform="translate(38, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">2</tspan></text></g><g class="tick" transform="translate(57, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">3</tspan></text></g><g class="tick" transform="translate(76, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">4</tspan></text></g><g class="tick" transform="translate(95, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">5</tspan></text></g><g class="tick" transform="translate(114, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">6</tspan></text></g><g class="tick" transform="translate(133, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">7</tspan></text></g><g class="tick" transform="translate(152, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">8</tspan></text></g><g class="tick" transform="translate(171, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">9</tspan></text></g><g class="tick" transform="translate(190, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">10</tspan></text></g><g class="tick" transform="translate(209, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">11</tspan></text></g><g class="tick" transform="translate(228, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">12</tspan></text></g><g class="tick" transform="translate(247, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">13</tspan></text></g><g class="tick" transform="translate(266, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">14</tspan></text></g><g class="tick" transform="translate(285, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">15</tspan></text></g><g class="tick" transform="translate(304, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">16</tspan></text></g><g class="tick" transform="translate(323, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">17</tspan></text></g><g class="tick" transform="translate(342, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">18</tspan></text></g><g class="tick" transform="translate(361, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">19</tspan></text></g><g class="tick" transform="translate(380, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">20</tspan></text></g><g class="tick" transform="translate(399, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">21</tspan></text></g><g class="tick" transform="translate(418, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">22</tspan></text></g><g class="tick" transform="translate(437, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">23</tspan></text></g><g class="tick" transform="translate(455, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="0"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">24</tspan></text></g><path class="domain" d="M0,6V0H455V6"></path></g><g class="c3-axis c3-axis-y" clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-yaxis)" transform="translate(0,0)" style="visibility: hidden; opacity: 1;"><text class="c3-axis-y-label" transform="rotate(-90)" style="text-anchor: end;" x="0" dx="-0.5em" dy="1.2em"></text><g class="tick" style="opacity: 1;" transform="translate(0,148)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">0</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,128)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">10</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,107)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">20</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,87)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">30</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,66)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">40</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,46)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">50</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,25)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">60</tspan></text></g><g class="tick" style="opacity: 1;" transform="translate(0,5)"><line x2="-6"></line><text x="-9" y="0" style="text-anchor: end;"><tspan x="-9" dy="3">70</tspan></text></g><path class="domain" d="M0,1H0V148H0"></path></g><g class="c3-axis c3-axis-y2" transform="translate(455,0)" style="visibility: hidden; opacity: 1;"><text class="c3-axis-y2-label" transform="rotate(-90)" style="text-anchor: end;" x="0" dx="-0.5em" dy="-0.5em"></text><g class="tick" transform="translate(0,148)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0</tspan></text></g><g class="tick" transform="translate(0,134)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.1</tspan></text></g><g class="tick" transform="translate(0,119)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.2</tspan></text></g><g class="tick" transform="translate(0,104)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.3</tspan></text></g><g class="tick" transform="translate(0,90)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.4</tspan></text></g><g class="tick" transform="translate(0,75)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.5</tspan></text></g><g class="tick" transform="translate(0,60)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.6</tspan></text></g><g class="tick" transform="translate(0,46)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.7</tspan></text></g><g class="tick" transform="translate(0,31)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.8</tspan></text></g><g class="tick" transform="translate(0,16)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">0.9</tspan></text></g><g class="tick" transform="translate(0,1)" style="opacity: 1;"><line x2="6" y2="0"></line><text x="9" y="0" style="text-anchor: start;"><tspan x="9" dy="3">1</tspan></text></g><path class="domain" d="M6,1H0V148H6"></path></g></g><g transform="translate(-0.5,160.5)" style="visibility: hidden;"><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-subchart)" class="c3-chart"><g class="c3-chart-bars"></g><g class="c3-chart-lines"></g></g><g clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip)" class="c3-brush" style="pointer-events: all; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"><rect class="background" x="0" width="455" style="visibility: hidden; cursor: crosshair;"></rect><rect class="extent" x="0" width="0" style="cursor: move;"></rect><g class="resize e" transform="translate(0,0)" style="cursor: ew-resize; display: none;"><rect x="-3" width="6" height="6" style="visibility: hidden;"></rect></g><g class="resize w" transform="translate(0,0)" style="cursor: ew-resize; display: none;"><rect x="-3" width="6" height="6" style="visibility: hidden;"></rect></g></g><g class="c3-axis-x" transform="translate(0,0)" clip-path="url(file:///D:/AntiSpam/tabler/tabler-gh-pages/index.html#c3-1569338568517-clip-xaxis)" style="visibility: hidden; opacity: 1;"><g class="tick" transform="translate(0, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="0"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">0</tspan></text></g><g class="tick" transform="translate(19, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">1</tspan></text></g><g class="tick" transform="translate(38, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">2</tspan></text></g><g class="tick" transform="translate(57, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">3</tspan></text></g><g class="tick" transform="translate(76, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">4</tspan></text></g><g class="tick" transform="translate(95, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">5</tspan></text></g><g class="tick" transform="translate(114, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">6</tspan></text></g><g class="tick" transform="translate(133, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">7</tspan></text></g><g class="tick" transform="translate(152, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">8</tspan></text></g><g class="tick" transform="translate(171, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">9</tspan></text></g><g class="tick" transform="translate(190, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">10</tspan></text></g><g class="tick" transform="translate(209, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">11</tspan></text></g><g class="tick" transform="translate(228, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">12</tspan></text></g><g class="tick" transform="translate(247, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">13</tspan></text></g><g class="tick" transform="translate(266, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">14</tspan></text></g><g class="tick" transform="translate(285, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">15</tspan></text></g><g class="tick" transform="translate(304, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">16</tspan></text></g><g class="tick" transform="translate(323, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">17</tspan></text></g><g class="tick" transform="translate(342, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">18</tspan></text></g><g class="tick" transform="translate(361, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">19</tspan></text></g><g class="tick" transform="translate(380, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">20</tspan></text></g><g class="tick" transform="translate(399, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">21</tspan></text></g><g class="tick" transform="translate(418, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">22</tspan></text></g><g class="tick" transform="translate(437, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="6"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: none;"><tspan x="0" dy=".71em" dx="0">23</tspan></text></g><g class="tick" transform="translate(455, 0)" style="opacity: 1;"><line x1="0" x2="0" y2="0"></line><text x="0" y="9" transform="" style="text-anchor: middle; display: block;"><tspan x="0" dy=".71em" dx="0">24</tspan></text></g><path class="domain" d="M0,6V0H455V6"></path></g></g><g transform="translate(19.5,13.5)"><g class="c3-legend-background"><rect height="208" width="95.125"></rect></g><g class="c3-legend-item c3-legend-item-data1" style="visibility: visible; cursor: pointer;"><text x="24" y="19" style="pointer-events: none;">Purchases</text><rect class="c3-legend-item-event" x="10" y="5" width="85.125" height="22" style="fill-opacity: 0;"></rect><line class="c3-legend-item-tile" x1="8" y1="14" x2="18" y2="14" stroke-width="10" style="stroke: rgb(70, 127, 207); pointer-events: none;"></line></g></g><text class="c3-title" x="227" y="0"></text></svg><div class="c3-tooltip-container" style="position: absolute; pointer-events: none; display: none; top: 116.833px; left: 335.5px;"><table class="c3-tooltip"><tbody><tr class="c3-tooltip-name--data1"><td class="name"><span style="background-color:#467fcf"></span>Purchases</td><td class="value">65</td></tr></tbody></table></div></div>
                    <div class="table-responsive">
                        <table class="table card-table table-striped table-vcenter">
                            <thead>
                            <tr>
                                <th colspan="2">User</th>
                                <th>Commit</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="w-1"><span class="avatar" style="background-image: url(./demo/faces/male/9.jpg)"></span></td>
                                <td>Ronald Bradley</td>
                                <td>Initial commit</td>
                                <td class="text-nowrap">May 6, 2018</td>
                                <td class="w-1"><a href="#" class="icon"><i class="fe fe-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td><span class="avatar">BM</span></td>
                                <td>Russell Gibson</td>
                                <td>Main structure</td>
                                <td class="text-nowrap">April 22, 2018</td>
                                <td><a href="#" class="icon"><i class="fe fe-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td><span class="avatar" style="background-image: url(./demo/faces/female/1.jpg)"></span></td>
                                <td>Beverly Armstrong</td>
                                <td>Left sidebar adjustments</td>
                                <td class="text-nowrap">April 15, 2018</td>
                                <td><a href="#" class="icon"><i class="fe fe-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td><span class="avatar" style="background-image: url(./demo/faces/male/4.jpg)"></span></td>
                                <td>Bobby Knight</td>
                                <td>Topbar dropdown style</td>
                                <td class="text-nowrap">April 8, 2018</td>
                                <td><a href="#" class="icon"><i class="fe fe-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td><span class="avatar" style="background-image: url(./demo/faces/female/11.jpg)"></span></td>
                                <td>Sharon Wells</td>
                                <td>Fixes #625</td>
                                <td class="text-nowrap">April 9, 2018</td>
                                <td><a href="#" class="icon"><i class="fe fe-trash"></i></a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    require(['c3', 'jquery'], function(c3, $) {
                        $(document).ready(function(){
                            var chart = c3.generate({
                                bindto: '#chart-development-activity', // id of chart wrapper
                                data: {
                                    columns: [
                                        // each columns data
                                        ['data1', 0, 5, 1, 2, 7, 5, 6, 8, 24, 7, 12, 5, 6, 3, 2, 2, 6, 30, 10, 10, 15, 14, 47, 65, 55]
                                    ],
                                    type: 'area', // default type of chart
                                    groups: [
                                        [ 'data1', 'data2', 'data3']
                                    ],
                                    colors: {
                                        'data1': tabler.colors["blue"]
                                    },
                                    names: {
                                        // name of each serie
                                        'data1': 'Purchases'
                                    }
                                },
                                axis: {
                                    y: {
                                        padding: {
                                            bottom: 0,
                                        },
                                        show: false,
                                        tick: {
                                            outer: false
                                        }
                                    },
                                    x: {
                                        padding: {
                                            left: 0,
                                            right: 0
                                        },
                                        show: false
                                    }
                                },
                                legend: {
                                    position: 'inset',
                                    padding: 0,
                                    inset: {
                                        anchor: 'top-left',
                                        x: 20,
                                        y: 8,
                                        step: 10
                                    }
                                },
                                tooltip: {
                                    format: {
                                        title: function (x) {
                                            return '';
                                        }
                                    }
                                },
                                padding: {
                                    bottom: 0,
                                    left: -1,
                                    right: -1
                                },
                                point: {
                                    show: false
                                }
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';