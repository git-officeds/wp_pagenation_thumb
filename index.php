<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>document</title>
    <style>
        /* Wordpressテーマ「Lightning」に沿ったレスポンシブ設定 */
        .container{
            margin: 20px auto;
            padding-left: 15px;
            padding-right: 15px;
            box-sizing: border-box;
        }
        @media (max-width: 575px){  /* containerサイズ100% */
           .container{ width: 100%; }
        }
        @media (min-width: 576px){  /* containerサイズ690px */
           .container{ width: 510px; }
        }
        @media (min-width: 768px){  /* containerサイズ690px */
           .container{ width: 690px; }
        }
        @media (min-width: 993px){  /* containerサイズ960px(サイドバー有：610px） */
           .container{ width: 960px; }
        }
        @media (min-width: 1201px){  /* containerサイズ1140px(サイドバー有：730px） */
           .container{ width: 1140px; }
        }
    </style>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="container">
        
        <!-- 表示見本：WordPress上ではショートコード [pagenation] が同じ構造のHTMLを出力します -->
        <section class="pagenation" data-total-pages="8">
            <ul class="pagenation__list">
                <!-- テキストのみ・80文字以内：本文をそのまま表示 -->
                <li class="pagenation__item">
                    <div class="pagenation__thumb">
                        <img src="default_thumb.jpg" alt="" loading="lazy">
                    </div>
                    <div class="pagenation__content">
                        <time class="pagenation__date" datetime="2026-10-09">2026年10月09日</time>
                        <h3 class="pagenation__title">年末年始休業のお知らせ</h3>
                        <p class="pagenation__body">誠に勝手ながら、12月29日から1月4日まで年末年始休業とさせていただきます。</p>
                    </div>
                </li>
                <!-- 80文字を超える：80文字で切り詰めてリンクを表示 -->
                <li class="pagenation__item">
                    <div class="pagenation__thumb">
                        <img src="default_thumb.jpg" alt="" loading="lazy">
                    </div>
                    <div class="pagenation__content">
                        <time class="pagenation__date" datetime="2026-09-24">2026年09月24日</time>
                        <h3 class="pagenation__title">新サービス提供開始のご案内</h3>
                        <p class="pagenation__body">平素は格別のご高配を賜り、厚く御礼申し上げます。このたび弊社では、お客様からのご要望にお応えし、新しいサービスの提供を開始いたしました。本サービスは従来の内容に加え<a class="pagenation__more" href="#">...詳しく読む</a></p>
                    </div>
                </li>
                <!-- 画像を含む：文字数にかかわらずリンクを表示（本文中の画像は非表示） -->
                <li class="pagenation__item">
                    <div class="pagenation__thumb">
                        <img src="default_thumb.jpg" alt="" loading="lazy">
                    </div>
                    <div class="pagenation__content">
                        <time class="pagenation__date" datetime="2026-09-01">2026年09月01日</time>
                        <h3 class="pagenation__title">展示会に出展しました</h3>
                        <p class="pagenation__body">先日開催された展示会の様子を写真でご紹介します。<a class="pagenation__more" href="#">...詳しく読む</a></p>
                    </div>
                </li>
            </ul>
            <!-- WordPress上では script.js が nav の中身を生成します -->
            <nav class="pagenation__nav" aria-label="ページネーション">
                <button type="button" class="pagenation__page pagenation__page--prev" data-page="2">前へ</button>
                <button type="button" class="pagenation__page" data-page="1" aria-label="1ページ目">1</button>
                <button type="button" class="pagenation__page" data-page="2" aria-label="2ページ目">2</button>
                <button type="button" class="pagenation__page is-current" data-page="3" aria-label="3ページ目" aria-current="page">3</button>
                <button type="button" class="pagenation__page" data-page="4" aria-label="4ページ目">4</button>
                <span class="pagenation__ellipsis">…</span>
                <button type="button" class="pagenation__page" data-page="8" aria-label="8ページ目">8</button>
                <button type="button" class="pagenation__page pagenation__page--next" data-page="4">次へ</button>
            </nav>
        </section>

    </div>
</body>
</html>