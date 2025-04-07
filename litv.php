<?php
/*
 * LiTV 直播源生成器魔改版
 * 版本：v1.0
 * 作者：leifeng
 * 最后修改：2025-03-30
 * 功能说明：
 *   - 访问 `http://yourserver/litv.php?token=xxxx` 返回完整 M3U 列表
 *   - 访问 `http://yourserver/litv.php?token=xxxx&id=频道ID` 返回指定频道的 M3U8 播放地址
 */

header('Content-Type: text/plain; charset=utf-8');
$SECRET_TOKEN = 'leifeng'; // 替换为你的实际token

// 检查token是否有效
if (!isset($_GET['token'])) {
    http_response_code(403);
    echo "Error: Access denied. Token is required.";
    exit;
}

if ($_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    echo "Error: Invalid token.";
    exit;
}

// 频道映射表（完整列表）
// 格式: '频道ID' => [参数1, 参数2, 'tvg-id', '频道名称', '台标URL', '分组名称(可选)']
$channels = [
	//新闻频道
	'4gtv-4gtv009' => [2, 7, '中天新闻', '中天新闻', 'https://logo.doube.eu.org/中天新闻.png',''],
	'4gtv-4gtv072' => [1, 2, 'TVBS新闻', 'TVBS新闻台', 'https://logo.doube.eu.org/TVBS新闻.png',''],
	'4gtv-4gtv152' => [1, 6, '东森新闻台', '东森新闻', 'https://logo.doube.eu.org/东森新闻台.png',''],
	'litv-ftv13' => [1, 7, '民视新闻台', '民视新闻台', 'https://logo.doube.eu.org/民视新闻台.png',''],
	'4gtv-4gtv075' => [1, 2, '镜电视新闻台', '镜新闻', 'https://logo.doube.eu.org/镜电视新闻台.png',''],
	'4gtv-4gtv010' => [1, 6, '非凡新闻', '非凡新闻', 'https://logo.doube.eu.org/非凡新闻.png',''],
	'4gtv-4gtv051' => [1, 2, '台视新闻台', '台视新闻', 'https://logo.doube.eu.org/台视新闻台.png',''],
	'4gtv-4gtv052' => [1, 2, '华视新闻', '华视新闻', 'https://logo.doube.eu.org/华视新闻.png',''],
	'4gtv-4gtv074' => [1, 2, '中视新闻台', '中视新闻', 'https://logo.doube.eu.org/中视新闻台.png',''],
	'litv-longturn14' => [1, 2, '寰宇新闻台', '寰宇新闻台', 'https://logo.doube.eu.org/寰宇新闻台.png',''],
	'4gtv-4gtv156' => [1, 6, '寰宇新闻台湾台', '寰宇新闻台湾台', 'https://logo.doube.eu.org/寰宇新闻台湾台.png',''],
	'litv-ftv10' => [1, 7, '半岛国际新闻', '半岛新闻', 'https://logo.doube.eu.org/半岛国际新闻.png',''],
	'litv-ftv03' => [1, 7, '美国之音', '美国之音', 'https://logo.doube.eu.org/美国之音.png',''],

	//财经频道
	'4gtv-4gtv153' => [1, 6, '东森财经新闻台', '东森财经新闻', 'https://logo.doube.eu.org/东森财经新闻台.png',''],
	'4gtv-4gtv048' => [1, 2, '非凡商业', '非凡商业台', 'https://logo.doube.eu.org/非凡商业.png',''],
	'4gtv-4gtv056' => [1, 2, '台视财经台', '台视财经', 'https://logo.doube.eu.org/台视财经台.png',''],
	'4gtv-4gtv104' => [1, 7, '第1商业台', '第1商业台', 'https://logo.doube.eu.org/第1商业台.png',''],

	//综合频道
	'4gtv-4gtv073' => [1, 2, 'TVBS', 'TVBS', 'https://logo.doube.eu.org/TVBS.png',''],
	'4gtv-4gtv066' => [1, 2, '台视', '台视', 'https://logo.doube.eu.org/台视.png',''],
	'4gtv-4gtv040' => [1, 6, '中视', '中视', 'https://logo.doube.eu.org/中视.png',''],
	'4gtv-4gtv041' => [1, 6, '华视', '华视', 'https://logo.doube.eu.org/华视.png',''],
	'4gtv-4gtv002' => [1, 10, '民视', '民视', 'https://logo.doube.eu.org/民视.png',''],
	'4gtv-4gtv155' => [1, 6, '民视', '民视', 'https://logo.doube.eu.org/民视.png',''],
	'4gtv-4gtv001' => [1, 6, '民视台湾台', '民视台湾台', 'https://logo.doube.eu.org/民视台湾台.png',''],
	'4gtv-4gtv003' => [1, 6, '民视第一台', '民视第一台', 'https://logo.doube.eu.org/民视第一台.png',''],
	'4gtv-4gtv109' => [1, 7, '中天亚洲', '中天亚洲台', 'https://logo.doube.eu.org/中天亚洲.png',''],
	'4gtv-4gtv046' => [1, 8, '靖天综合台', '靖天综合台', 'https://logo.doube.eu.org/靖天综合台.png',''],
	'4gtv-4gtv063' => [1, 6, '靖天国际台', '靖天国际台', 'https://logo.doube.eu.org/靖天国际台.png',''],
	'4gtv-4gtv065' => [1, 8, '靖天资讯台', '靖天资讯台', 'https://logo.doube.eu.org/靖天资讯台.png',''],
	'4gtv-4gtv043' => [1, 6, '客家电视台', '客家电视台', 'https://logo.doube.eu.org/客家电视台.png',''],
	'4gtv-4gtv079' => [1, 2, 'ARIRANG', '阿里郎', 'https://logo.doube.eu.org/ARIRANG.png',''],
	'4gtv-4gtv084' => [1, 6, '国会频道1', '国会频道1', 'https://logo.doube.eu.org/国会频道1.png',''],
	'4gtv-4gtv085' => [1, 5, '国会频道2', '国会频道2', 'https://logo.doube.eu.org/国会频道2.png',''],

	//娱乐综艺频道
	'4gtv-4gtv068' => [1, 7, 'TVBS欢乐台', 'TVBS欢乐台', 'https://logo.doube.eu.org/TVBS欢乐台.png',''],
	'4gtv-4gtv067' => [1, 8, 'TVBS精采台', 'TVBS精采台', 'https://logo.doube.eu.org/TVBS精采台.png',''],
	'4gtv-4gtv070' => [1, 7, 'ELTV娱乐', '爱尔达娱乐', 'https://logo.doube.eu.org/ELTV娱乐.png',''],
	'4gtv-4gtv004' => [1, 8, '民视综艺台', '民视综艺台', 'https://logo.doube.eu.org/民视综艺台.png',''],
	'4gtv-4gtv039' => [1, 7, '八大综艺', '八大综艺台', 'https://logo.doube.eu.org/八大综艺.png',''],
	'4gtv-4gtv034' => [1, 6, '八大精彩', '八大精彩台', 'https://logo.doube.eu.org/八大精彩.png',''],
	'4gtv-4gtv054' => [1, 8, '靖天欢乐台', '靖天欢乐台', 'https://logo.doube.eu.org/靖天欢乐台.png',''],
	'4gtv-4gtv062' => [1, 8, '靖天育乐台', '靖天育乐台', 'https://logo.doube.eu.org/靖天育乐台.png',''],
	'4gtv-4gtv064' => [1, 8, '中视菁采台', '中视菁采台', 'https://logo.doube.eu.org/中视菁采台.png',''],
	'4gtv-4gtv006' => [1, 9, '猪哥亮歌厅秀', '猪哥亮歌厅秀', 'https://logo.doube.eu.org/猪哥亮歌厅秀.png',''],

	//电影频道
	'4gtv-4gtv011' => [1, 6, '影迷數位電影台', '影迷數位電影台', 'https://logo.doube.eu.org/影迷數位電影台.png',''],
	'4gtv-4gtv017' => [1, 6, 'amc电影台', 'amc电影台', 'https://logo.doube.eu.org/amc电影台.png',''],
	'4gtv-4gtv061' => [1, 7, '靖天电影台', '靖天电影台', 'https://logo.doube.eu.org/靖天电影台.png',''],
	'4gtv-4gtv055' => [1, 8, '靖天映画', '靖天映画', 'https://logo.doube.eu.org/靖天映画.png',''],
	'4gtv-4gtv049' => [1, 8, '采昌影剧台', '采昌影剧台', 'https://logo.doube.eu.org/采昌影剧台.png',''],
	'litv-ftv09' => [1, 2, '民视影剧台', '民视影剧', 'https://logo.doube.eu.org/民视影剧台.png',''],
	'litv-longturn03' => [5, 6, '龙华电影台', '龙华电影', 'https://logo.doube.eu.org/龙华电影台.png',''],
	'litv-longturn02' => [5, 2, '龙华洋片台', '龙华洋片', 'https://logo.doube.eu.org/龙华洋片台.png',''],

	//戏剧频道
	'4gtv-4gtv042' => [1, 6, '公视戏剧', '公视戏剧', 'https://logo.doube.eu.org/公视戏剧.png',''],
	'4gtv-4gtv045' => [1, 6, '靖洋戏剧台', '靖洋戏剧台', 'https://logo.doube.eu.org/靖洋戏剧台.png',''],
	'4gtv-4gtv058' => [1, 8, '靖天戏剧台', '靖天戏剧台', 'https://logo.doube.eu.org/靖天戏剧台.png',''],
	'4gtv-4gtv080' => [1, 6, '中视经典台', '中视经典台', 'https://logo.doube.eu.org/中视经典台.png',''],
	'4gtv-4gtv047' => [1, 8, '靖天日本台', '靖天日本台', 'https://logo.doube.eu.org/靖天日本台.png',''],
	'litv-longturn18' => [5, 6, '龙华戏剧台', '龙华戏剧', 'https://logo.doube.eu.org/龙华戏剧台.png',''],
	'litv-longturn11' => [5, 2, '龙华日韩台', '龙华日韩', 'https://logo.doube.eu.org/龙华日韩台.png',''],
	'litv-longturn12' => [5, 2, '龙华偶像台', '龙华偶像', 'https://logo.doube.eu.org/龙华偶像台.png',''],
	'litv-longturn21' => [5, 2, '龙华经典台', '龙华经典', 'https://logo.doube.eu.org/龙华经典台.png',''],
	'litv-longturn22' => [5, 2, '台湾戏剧台', '台湾戏剧台', 'https://logo.doube.eu.org/台湾戏剧台.png',''],

	//体育频道
	'4gtv-4gtv014' => [1, 5, '时尚运动X', '时尚运动X', 'https://logo.doube.eu.org/时尚运动X.png',''],
	'4gtv-4gtv053' => [1, 8, 'GINXEsportsTV', 'GinxTV', 'https://logo.doube.eu.org/GINXEsportsTV.png',''],
	'4gtv-4gtv101' => [1, 5, '智林体育台', '智林体育台', 'https://logo.doube.eu.org/智林体育台.png',''],
	'litv-longturn04' => [5, 2, '博斯魅力', '博斯魅力', 'https://logo.doube.eu.org/博斯魅力.png',''],
	'litv-longturn05' => [5, 2, '博斯高球1', '博斯高球1', 'https://logo.doube.eu.org/博斯高球1.png',''],
	'litv-longturn06' => [5, 2, '博斯高球2', '博斯高球2', 'https://logo.doube.eu.org/博斯高球2.png',''],
	'litv-longturn07' => [5, 2, '博斯运动1', '博斯运动1', 'https://logo.doube.eu.org/博斯运动1.png',''],
	'litv-longturn08' => [5, 2, '博斯运动2', '博斯运动2', 'https://logo.doube.eu.org/博斯运动2.png',''],
	'litv-longturn09' => [5, 2, '博斯网球1', '博斯网球', 'https://logo.doube.eu.org/博斯网球1.png',''],
	'litv-longturn10' => [5, 2, '博斯无限1', '博斯无限', 'https://logo.doube.eu.org/博斯无限1.png',''],
	'litv-longturn13' => [4, 2, '博斯无限2', '博斯无限2', 'https://logo.doube.eu.org/博斯无限2.png',''],

	//纪实/知识/旅游频道
	'4gtv-4gtv013' => [1, 6, '视纳华仁纪实', '视纳华仁纪实', 'https://logo.doube.eu.org/视纳华仁纪实.png',''],
	'4gtv-4gtv016' => [1, 6, 'Globetrotter', 'Globetrotter', 'https://logo.doube.eu.org/Globetrotter.png',''],
	'4gtv-4gtv018' => [1, 6, '达文西频道', '达文西频道', 'https://logo.doube.eu.org/达文西频道.png',''],
	'4gtv-4gtv076' => [1, 2, '亚洲旅游台', '亚洲旅游台', 'https://logo.doube.eu.org/亚洲旅游台.png',''],
	'litv-ftv07' => [1, 7, '民视旅游台', '民视旅游', 'https://logo.doube.eu.org/民视旅游台.png',''],
	'litv-longturn19' => [5, 2, 'Smart知识台', 'Smart知识台', 'https://logo.doube.eu.org/Smart知识台.png',''],

	//儿童/卡通频道
	'4gtv-4gtv044' => [1, 8, '靖天卡通台', '靖天卡通台', 'https://logo.doube.eu.org/靖天卡通台.png',''],
	'4gtv-4gtv057' => [1, 8, '靖洋卡通台', '靖洋卡通台', 'https://logo.doube.eu.org/靖洋卡通台.png',''],
	'litv-longturn01' => [4, 2, '龙华卡通台', '龙华卡通', 'https://logo.doube.eu.org/龙华卡通台.png',''],

	//音乐/艺术频道
	'4gtv-4gtv059' => [1, 6, 'CLASSICA古典乐', '古典音乐台', 'https://logo.doube.eu.org/CLASSICA古典乐.png',''],
	'4gtv-4gtv082' => [1, 6, 'TraceUrban', 'TRACE URBAN', 'https://logo.doube.eu.org/TraceUrban.png',''],
	'4gtv-4gtv083' => [1, 6, 'MezzoLiveHD', 'MEZZO LIVE', 'https://logo.doube.eu.org/MezzoLiveHD.png',''],

	//教育/宗教频道
	'litv-ftv16' => [1, 2, '好消息1', '好消息1', 'https://logo.doube.eu.org/好消息1.png',''],
	'litv-ftv17' => [1, 2, '好消息2', '好消息2', 'https://logo.doube.eu.org/好消息2.png',''],
	'litv-longturn20' => [5, 6, 'ELTV生活英语台', '生活英语台', 'https://logo.doube.eu.org/ELTV生活英语台.png','']
];

$id = isset($_GET['id']) ? $_GET['id'] : null;

// 动态获取基础 URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host";

// 无参数时返回完整 M3U 频道列表
if (!$id) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "#EXTM3U\n";
    foreach ($channels as $key => $value) {
        $group = (isset($value[5]) && $value[5] !== '') ? $value[5] : '台湾频道';
        echo '#EXTINF:-1 tvg-id="'.$value[2].'" tvg-name="'.$value[3].'" tvg-logo="'.$value[4].'" group-title="'.$group.'",'.$value[3]."\n";
        echo "$base_url/litv.php?token=" . urlencode($SECRET_TOKEN) . "&id=" . urlencode($key) . "\n";
    }
    exit;
}

// 检查频道 ID 是否有效
if (!isset($channels[$id])) {
    http_response_code(404);
    echo "Error: Channel not found.";
    exit;
}

// 生成 M3U8 播放列表
$timestamp = intval(time() / 4 - 355017625);
$t = $timestamp * 4;
$current = "#EXTM3U\n";
$current .= "#EXT-X-VERSION:3\n";
$current .= "#EXT-X-TARGETDURATION:4\n";
$current .= "#EXT-X-MEDIA-SEQUENCE:{$timestamp}\n";

for ($i = 0; $i < 3; $i++) {
    $current .= "#EXTINF:4,\n";
    $current .= "https://ntd-tgc.cdn.hinet.net/live/pool/{$id}/litv-pc/{$id}-avc1_6000000={$channels[$id][0]}-mp4a_134000_zho={$channels[$id][1]}-begin={$t}0000000-dur=40000000-seq={$timestamp}.ts\n";
    $timestamp++;
    $t += 4;
}

header('Content-Type: application/vnd.apple.mpegurl');
header('Content-Disposition: inline; filename=' . $id . '.m3u8');
header('Content-Length: ' . strlen($current));
echo $current;
?>