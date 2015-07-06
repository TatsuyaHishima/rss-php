<?php

    // ライブラリの読み込み
    require_once "php/Feed.php";

    // htmlspecialcharsをヒアドキュメント内で使えるようにするためのクラス
    class HSC_Class {
        public function enc($output){
            return htmlspecialchars($output);
        }
    }
    $hsc = new HSC_Class();

    // amebloの画像リンクを取得する関数
    function get_img_url_ameblo($link, $noimage_path) {
        require_once "php/simple_html_dom.php";

        $html = file_get_html($link);

        // 配列の初期化
        $imgurl = array();
        $img_url_list = array();

        // 画像位置の検索 人によって変わる可能性あり（div.contents imgの場合等があり）
        foreach ($html->find('div.articleText img') as $content) {
            $content = $content->src;
            $imgurl[] = $content;
        }
        // 不要な画像の排除
        foreach ($imgurl as $value) {
            if ( !stristr($value , 'stat.ameba.jp/blog/ucs/img') and !stristr($value , 'emoji.ameba.jp') ) {
                $img_url_list[] = $value;
            }
        }

        // 記事内に画像がない場合の画像の指定
        if ($img_url_list[0] == false) {
            $img_url_list[0] = $noimage_path;
        }

        return $img_url_list[0];
    }

    // hack here
    // 取得するフィードのURLを指定
    $url = "http://feedblog.ameba.jp/rss/ameblo/ebihara-eri/rss20.xml";

    // hack here
    // feedの読み込み数
    $MAX_feed = 3;

    // hack here
    // 画像が無かった場合に表示する画像のパス
    $no_image_path = "./img/no_image.jpg";

    $feed_count = 0; // 初期化

    // RSSを読み込む
    $rss = Feed::loadRss($url);

    echo '<ul>' . "\n";

    foreach($rss->item as $item){
        if ($feed_count >= $MAX_feed) { // 読み込み数を超えた場合は終了
            break;
        }
        $feed_count++;

        // 各エントリーの処理
        $title = $item->title;  // タイトル
        $link = $item->link;    // リンク
        $description = $item->description;  // 詳細
        $img_url = get_img_url_ameblo($link, $no_image_path);

        // 日付の取得(UNIX TIMESTAMP)
        if (isset($item->pubDate) && !empty($item->pubDate)){
            $timestamp = strtotime($item->pubDate);
        }
        elseif (isset($item->date_timestamp) && !empty($item->date_timestamp)){
            $timestamp = $item->date_timestamp;
        }
        elseif (isset($item->{'dc:date'}) && !empty($item->{'dc:date'})){
            $timestamp = strtotime($item->{'dc:date'});
        }
        elseif (isset($item->published) && !empty($item->published)){
            $timestamp = strtotime($item->published);
        }
        elseif (isset($item->issued) && !empty($item->issued)){
            $timestamp = strtotime($item->issued);
        }
        else{
            $timestamp = time();
        }

        // 表示
        echo <<< EOP
        <li class="RSS__li clearfix">
            <div class="RSS__li__img">
                <img src="$img_url">
            </div>
            <div class="RSS__li__detail">
                <h5 class="RSS__li__detail__title">{$hsc->enc(htmlspecialchars($title))}</h5>
                <p class="RSS__li__detail__date">({$hsc->enc(date("Y/m/d",$timestamp))})</p>
                <a class="RSS__li__detail__link" target="_blank" href="{$hsc->enc(htmlspecialchars($link))}"></a>
            </div>
        </li>
EOP;
        echo "\n";
    }
    echo '</ul>' . "\n";


?>
