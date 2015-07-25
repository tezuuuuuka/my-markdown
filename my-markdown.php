<?php
/*
Plugin Name: MyMarkdown
Plugin URI: http://imys.la/shun/
Description: Markdown the content.
Version: 0.1
Author: Masatoshi TEZUKA
Author URI: http://imys.la/shun/
License: GPL2
*/

add_filter( 'the_content', 'my_markdown', 7 );

function my_markdown( $content ) {
    $convert = array(
        '/^\*?\[[^]^]+\]: .*$/m' => '',

        '/(\*\*|__)([^ *_][^\r\n*_]*)\1/m' => '<strong>$2</strong>',
        '/(\*|_)([^ *_][^\r\n*_]*)\1/m' => '<em>$2</em>',

        '/\[!\[([^\]]+)\]\(([^ ]+)\)\]\(([^ ]+) "([^"]+)"\)/'
                => '<a href="$3" title="$4"><img src="$2" alt="$1" /></a>',

        '/!\[([^\]]+)\]\(([^ ]+) "([^"]+)"\)/' => '<img src="$2" alt="$1" title="$3" />',
        '/\[([^\]]+)\]\(([^ ]+) "([^"]+)"\)/' => '<a href="$2" title="$3">$1</a>',

        '/((^> +.*\r?\n)+)/m' => "<blockquote>\n$1\n</blockquote>",
        '/((^> +> +.*\r?\n)+)/m' => "<blockquote>\n$1\n</blockquote>",
        '/^(?:> +)+(.*)/m' => '$1',

        '/(((^[0-9]+\. .*\r?\n)+(^ +(\*|-) .*\r?\n)*)+)/m' => "<ol>\n$1\n</ol>\n",
        '/(((^(\*|-) .*\r?\n)+(^ +[0-9]+\. .*\r?\n)*)+)/m' => "<ul>\n$1\n</ul>\n",
        '/((^ +[0-9]+\. .*\r?\n)+)/m' => "<ol>\n$1\n</ol>\n",
        '/((^ +(\*|-) .*\r?\n)+)/m' => "<ul>\n$1\n</ul>\n",
        '/^ *([0-9]+\.|\*|-) (.*)$/m' => '<li>$2</li>',

        '/(?:\n)((^ {2}.*\r?\n)+)(?:\r?\n)/m' => "<pre>\n$1</pre>",
        '/^ {2}(.*)$/m' => '$1',

        '/(~~~~|```)(\w*)([^~`]*)\1/sm' => '[code language="$2"]$3[/code]',
        '/\[code language=""\]/' => '[code]',
        '/`([^`]*)`/s' => '<code>$1</code>',

        '/^#{1} ([^#\r\n]+)[^\r\n]*/m' => '<h1>$1</h1>',
        '/^#{2} ([^#\r\n]+)[^\r\n]*/m' => '<h2>$1</h2>',
        '/^#{3} ([^#\r\n]+)[^\r\n]*/m' => '<h3>$1</h3>',
        '/^#{4} ([^#\r\n]+)[^\r\n]*/m' => '<h4>$1</h4>',
        '/^#{5} ([^#\r\n]+)[^\r\n]*/m' => '<h5>$1</h5>',
        '/^#{6} ([^#\r\n]+)[^\r\n]*/m' => '<h6>$1</h6>',

        '/((?:^.*\r?\n: *[^ ].*$(\r?\n)*)+)/m' => "<dl>\n$1\n</dl>",
        '/^(.*)\r?\n: *([^ ].*)$/m' => '<dt>$1</dt><dd>$2</dd>',
    );
    preg_match_all('/^\[(\d+)\]: ([^ ]+) "([^"]+)"/m', $content, $matches, PREG_SET_ORDER);
    foreach ( $matches as $m ) {
        $convert["/!\[([^\]]+)\]\[{$m[1]}\]/"] = "<img src=\"{$m[2]}\" alt=\"$1\" title=\"{$m[3]}\" />";
        $convert["/\[([^\]]+)\]\[{$m[1]}\]/"] = "<a href=\"{$m[2]}\" title=\"{$m[3]}\">$1</a>";
    }
    preg_match_all('/^\*\[([^\]]+)\]: ([^\r\n]*)/m', $content, $matches, PREG_SET_ORDER);
    foreach ( $matches as $m ) {
        $convert["/(?<=\W){$m[1]}(?=\W)/"] = "<abbr title=\"{$m[2]}\">{$m[1]}</abbr>";
    }
    preg_match_all('/^\[\^(\d+)\]: [^\r\n]*/m', $content, $matches, PREG_SET_ORDER);
    $num = array();
    foreach ( $matches as $m ) {
        $num[] = $m[1];
    }
    foreach ( $num as $k => $n ) {
        $i = $k + 1;
        $hash = get_the_ID() .'-'. $i;
        $convert["/\[\^{$n}\](?!:)/"]
                = "<sup id=\"ref:{$hash}\"><a rel=\"footnote\" href=\"#fn:{$hash}\">{$i}</a></sup>";
        $convert["/^\[\^{$n}\]: ([^\\r\\n]*)(.*)/sm"]
                = "$2\n<li id=\"fn:{$hash}\">$1<a rev=\"footnote\" href=\"#ref:{$hash}\">[Ret]</a></li>";
    }
    $convert['/((^<li id="fn:.*)+)/m'] = '<div class="footnotes"><hr /><ol>$1</ol></div>';
    return preg_replace( array_keys($convert), array_values($convert), $content);
} // end function my_markdown

