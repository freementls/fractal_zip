<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Variable functions - Manual</title>
 <style type="text/css" media="all">
  @import url("@w{2XX58MCD}");
  @import url("@w{884KPP5P}");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("@w{M98RFPWS}");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="@w{NGWYKJ8F}" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="language.functions.php" />
 <link rel="prev" href="functions.returning-values.php" />
 <link rel="next" href="functions.internal.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions.variable-functions" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/functions.variable-functions.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{RN23NHC3}" />
 <meta http-equiv="Content-language" content="en" />
            <script type="text/javascript" src="@w{ME5H2G8Y}"></script>
            <script type="text/javascript" src="@w{BYSKBGP9}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var toggleImage = function(elem) {
        if ($(elem).hasClass("shown")) {
            $(elem).removeClass("shown").addClass("hidden");
            $("img", elem).attr("src", "/images/notes-add.gif");
        }
        else {
            $(elem).removeClass("hidden").addClass("shown");
            $("img", elem).attr("src", "/images/notes-reject.gif");
        }
    };

    $(".soft-deprecation-notice h1.title").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='minimize' /></a> ");
    });
    $(".refsect1 h3.title").each(function() {
        url = "@w{BD87E369}" + $(this).parent().parent().attr("id") + "%23" + $(this).parent().attr("id");
        $(this).parent().prepend("<div class='reportbug'><a href='" + url + "'>Report a bug</a></div>");
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $("#usernotes .head").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $(".soft-deprecation-notice h1.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $(".refsect1 h3.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $("#usernotes .head .toggler").click(function() {
        $(this).parent().next().slideToggle("slow");
        toggleImage(this);
        return false;
    });
});
</script>

</head>
<body>

<div id="headnav">
 <a href="/" rel="home"><img src="@w{BJ2SG82M}"
 alt="PHP" width="120" height="67" id="phplogo" /></a>
 <div id="headmenu">
  <a href="/downloads.php">downloads</a> |
  <a href="/docs.php">documentation</a> |
  <a href="/FAQ.php">faq</a> |
  <a href="/support.php">getting help</a> |
  <a href="/mailing-lists.php">mailing lists</a> |
  <a href="/license">licenses</a> |
  <a href="@w{WEGCK3BV}">wiki</a> |
  <a href="@w{JBVFFY7T}">reporting bugs</a> |
  <a href="/sites.php">php.net sites</a> |
  <a href="/conferences/">conferences</a> |
  <a href="/my.php">my php.net</a>
 </div>
</div>

<div id="headsearch">
 <form method="post" action="/search.php" id="topsearch">
  <p>
   <span title="Keyboard shortcut: Alt+S (Win), Ctrl+S (Apple)">
    <span class="shortkey">s</span>earch for
   </span>
   <input type="text" name="pattern" value="" size="30" accesskey="s" />
   <span>in the</span>
   <select name="show">
    <option value="all"      >all php.net sites</option>
    <option value="local"    >this mirror only</option>
    <option value="quickref" selected="selected">function list</option>
    <option value="manual"   >online documentation</option>
    <option value="bugdb"    >bug database</option>
    <option value="news_archive">Site News Archive</option>
    <option value="changelogs">All Changelogs</option>
    <option value="pear"     >just pear.php.net</option>
    <option value="pecl"     >just pecl.php.net</option>
    <option value="talks"    >just talks.php.net</option>
    <option value="maillist" >general mailing list</option>
    <option value="devlist"  >developer mailing list</option>
    <option value="phpdoc"   >documentation mailing list</option>
   </select>
   <input type="image"
          src="@w{XXWWP636}"
          class="submit" alt="search" />
   <input type="hidden" name="lang" value="en" />
  </p>
 </form>
</div>

<div id="layout_2">
 <div id="leftbar">
<!--UdmComment-->
<ul class="toc">
 <li class="header home"><a href="index.php">PHP Manual</a></li>
 <li class="header up"><a href="langref.php">Language Reference</a></li>
 <li class="header up"><a href="language.functions.php">Functions</a></li>
 <li><a href="functions.user-defined.php">User-defined functions</a></li>
 <li><a href="functions.arguments.php">Function arguments</a></li>
 <li><a href="functions.returning-values.php">Returning values</a></li>
 <li class="active"><a href="functions.variable-functions.php">Variable functions</a></li>
 <li><a href="functions.internal.php">Internal (built-in) functions</a></li>
 <li><a href="functions.anonymous.php">Anonymous functions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="functions.internal.php">Internal (built-in) functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.returning-values.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Returning values</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.variable-functions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/functions.variable-functions.php">Brazilian Portuguese</option>
    <option value="zh/functions.variable-functions.php">Chinese (Simplified)</option>
    <option value="fr/functions.variable-functions.php">French</option>
    <option value="de/functions.variable-functions.php">German</option>
    <option value="ja/functions.variable-functions.php">Japanese</option>
    <option value="pl/functions.variable-functions.php">Polish</option>
    <option value="ro/functions.variable-functions.php">Romanian</option>
    <option value="ru/functions.variable-functions.php">Russian</option>
    <option value="fa/functions.variable-functions.php">Persian</option>
    <option value="es/functions.variable-functions.php">Spanish</option>
    <option value="tr/functions.variable-functions.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="functions.variable-functions" class="sect1">
   <h2 class="title">Variable functions</h2>

   <p class="para">
    PHP supports the concept of variable functions. This means that if
    a variable name has parentheses appended to it, PHP will look for
    a function with the same name as whatever the variable evaluates
    to, and will attempt to execute it. Among other things, this can
    be used to implement callbacks, function tables, and so forth.
   </p>
   <p class="para">
    Variable functions won&#039;t work with language constructs such 
    as  <span class="function"><a href="function.echo.php" class="function">echo</a></span>,  <span class="function"><a href="function.print.php" class="function">print</a></span>,
     <span class="function"><a href="function.unset.php" class="function">unset()</a></span>,  <span class="function"><a href="function.isset.php" class="function">isset()</a></span>,
     <span class="function"><a href="function.empty.php" class="function">empty()</a></span>,  <span class="function"><a href="function.include.php" class="function">include</a></span>,
     <span class="function"><a href="function.require.php" class="function">require</a></span> and the like. Utilize wrapper functions to make
    use of any of these constructs as variable functions.
   </p>
   <p class="para">
    <div class="example" id="example-158">
     <p><strong>Example #1 Variable function example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"In&nbsp;foo()&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />}<br /><br />function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">(</span><span style="color: #0000BB">$arg&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">''</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"In&nbsp;bar();&nbsp;argument&nbsp;was&nbsp;'</span><span style="color: #0000BB">$arg</span><span style="color: #DD0000">'.&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;a&nbsp;wrapper&nbsp;function&nbsp;around&nbsp;echo<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">echoit</span><span style="color: #007700">(</span><span style="color: #0000BB">$string</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$string</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$func&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$func</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;foo()<br /><br /></span><span style="color: #0000BB">$func&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$func</span><span style="color: #007700">(</span><span style="color: #DD0000">'test'</span><span style="color: #007700">);&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;bar()<br /><br /></span><span style="color: #0000BB">$func&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'echoit'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$func</span><span style="color: #007700">(</span><span style="color: #DD0000">'test'</span><span style="color: #007700">);&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;echoit()<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    An object method can also be called with the variable functions syntax.
    <div class="example" id="example-159">
     <p><strong>Example #2 Variable method example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">Variable</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$name&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Bar'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$name</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;the&nbsp;Bar()&nbsp;method<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">Bar</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;is&nbsp;Bar"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$funcname&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Variable"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$funcname</span><span style="color: #007700">();&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;$foo-&gt;Variable()<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    When calling static methods, the function call is stronger than the static property operator:
    <div class="example" id="example-160">
     <p><strong>Example #3 Variable method example with static properties</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;</span><span style="color: #0000BB">$variable&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'static&nbsp;property'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">Variable</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Method&nbsp;Variable&nbsp;called'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />echo&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">::</span><span style="color: #0000BB">$variable</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;prints&nbsp;'static&nbsp;property'.&nbsp;It&nbsp;does&nbsp;need&nbsp;a&nbsp;$variable&nbsp;in&nbsp;this&nbsp;scope.<br /></span><span style="color: #0000BB">$variable&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Variable"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">Foo</span><span style="color: #007700">::</span><span style="color: #0000BB">$variable</span><span style="color: #007700">();&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;calls&nbsp;$foo-&gt;Variable()&nbsp;reading&nbsp;$variable&nbsp;in&nbsp;this&nbsp;scope.<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
      
   <p class="para">
    See also  <span class="function"><a href="function.call-user-func.php" class="function">call_user_func()</a></span>,
    <a href="language.variables.variable.php" class="link">
    variable variables</a> and  <span class="function"><a href="function.function-exists.php" class="function">function_exists()</a></span>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="functions.internal.php">Internal (built-in) functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.returning-values.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Returning values</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.variable-functions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=functions.variable-functions&amp;redirect=@w{RN23NHC3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.variable-functions&amp;redirect=@w{RN23NHC3}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Variable functions</strong>
 </div><div id="allnotes">
 <a name="104641"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#104641" class="date">27-Jun-2011 11:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$ wget <a href="http://www.php.net/get/php_manual_en.tar.gz/from/a/mirror" rel="nofollow" target="_blank">http://www.php.net/get/php_manual_en.tar.gz/from/a/mirror</a><br />
$ grep -l "\$\.\.\." php-chunked-xhtml/function.*.html<br />
<br />
List of functions that accept variable arguments.<br />
<span class="default">&lt;?php<br />
array_diff_assoc</span><span class="keyword">()<br />
</span><span class="default">array_diff_key</span><span class="keyword">()<br />
</span><span class="default">array_diff_uassoc</span><span class="keyword">()<br />
array()<br />
</span><span class="default">array_intersect_ukey</span><span class="keyword">()<br />
</span><span class="default">array_map</span><span class="keyword">()<br />
</span><span class="default">array_merge</span><span class="keyword">()<br />
</span><span class="default">array_merge_recursive</span><span class="keyword">()<br />
</span><span class="default">array_multisort</span><span class="keyword">()<br />
</span><span class="default">array_push</span><span class="keyword">()<br />
</span><span class="default">array_replace</span><span class="keyword">()<br />
</span><span class="default">array_replace_recursive</span><span class="keyword">()<br />
</span><span class="default">array_unshift</span><span class="keyword">()<br />
</span><span class="default">call_user_func</span><span class="keyword">()<br />
</span><span class="default">call_user_method</span><span class="keyword">()<br />
</span><span class="default">compact</span><span class="keyword">()<br />
</span><span class="default">dba_open</span><span class="keyword">()<br />
</span><span class="default">dba_popen</span><span class="keyword">()<br />
echo()<br />
</span><span class="default">forward_static_call</span><span class="keyword">()<br />
</span><span class="default">fprintf</span><span class="keyword">()<br />
</span><span class="default">fscanf</span><span class="keyword">()<br />
</span><span class="default">httprequestpool_construct</span><span class="keyword">()<br />
</span><span class="default">ibase_execute</span><span class="keyword">()<br />
</span><span class="default">ibase_set_event_handler</span><span class="keyword">()<br />
</span><span class="default">ibase_wait_event</span><span class="keyword">()<br />
isset()<br />
list()<br />
</span><span class="default">maxdb_stmt_bind_param</span><span class="keyword">()<br />
</span><span class="default">maxdb_stmt_bind_result</span><span class="keyword">()<br />
</span><span class="default">mb_convert_variables</span><span class="keyword">()<br />
</span><span class="default">newt_checkbox_tree_add_item</span><span class="keyword">()<br />
</span><span class="default">newt_grid_h_close_stacked</span><span class="keyword">()<br />
</span><span class="default">newt_grid_h_stacked</span><span class="keyword">()<br />
</span><span class="default">newt_grid_v_close_stacked</span><span class="keyword">()<br />
</span><span class="default">newt_grid_v_stacked</span><span class="keyword">()<br />
</span><span class="default">newt_win_choice</span><span class="keyword">()<br />
</span><span class="default">newt_win_entries</span><span class="keyword">()<br />
</span><span class="default">newt_win_menu</span><span class="keyword">()<br />
</span><span class="default">newt_win_message</span><span class="keyword">()<br />
</span><span class="default">newt_win_ternary</span><span class="keyword">()<br />
</span><span class="default">pack</span><span class="keyword">()<br />
</span><span class="default">printf</span><span class="keyword">()<br />
</span><span class="default">register_shutdown_function</span><span class="keyword">()<br />
</span><span class="default">register_tick_function</span><span class="keyword">()<br />
</span><span class="default">session_register</span><span class="keyword">()<br />
</span><span class="default">setlocale</span><span class="keyword">()<br />
</span><span class="default">sprintf</span><span class="keyword">()<br />
</span><span class="default">sscanf</span><span class="keyword">()<br />
unset()<br />
</span><span class="default">var_dump</span><span class="keyword">()<br />
</span><span class="default">w32api_deftype</span><span class="keyword">()<br />
</span><span class="default">w32api_init_dtype</span><span class="keyword">()<br />
</span><span class="default">w32api_invoke_function</span><span class="keyword">()<br />
</span><span class="default">wddx_add_vars</span><span class="keyword">()<br />
</span><span class="default">wddx_serialize_vars</span><span class="keyword">()<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="103749"></a>
 <div class="note">
  <strong class='user'>imurnane at internode on net</strong>
  <a href="#103749" class="date">02-May-2011 03:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Create and call a dynamically named function<br />
<br />
<span class="default">&lt;?php<br />
$tmp </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
$</span><span class="default">$tmp </span><span class="keyword">= function() {<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$tmp</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$tmp</span><span class="keyword">;<br />
}; <br />
<br />
$</span><span class="default">$tmp</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Outputs "foo"</span>
</code></div>
  </div>
 </div>
 <a name="102150"></a>
 <div class="note">
  <strong class='user'>michalmojz at gmail dot com</strong>
  <a href="#102150" class="date">29-Jan-2011 01:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can make dynamic functions as well. <br />
<br />
For example<br />
<br />
<span class="default">&lt;?php<br />
$myFunction </span><span class="keyword">= function() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="default">1</span><span class="keyword">;<br />
};<br />
<br />
if(</span><span class="default">is_callable</span><span class="keyword">(</span><span class="default">$myFunction</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$myFunction</span><span class="keyword">();<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97694"></a>
 <div class="note">
  <strong class='user'>AnonymousPoster at disposeamail dot com</strong>
  <a href="#97694" class="date">03-May-2010 02:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Variable functions allows higher-order programming.<br />
<br />
Here is the classical map example.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
&nbsp;* Map function. At each $element of the $list, calls $fun([$arg1,[$arg2,[...,]],$element,$accumulator),<br />
&nbsp;*&nbsp; &nbsp; &nbsp; stores the return value into $accumulator for the next loop. Returns the last return value of the function,<br />
&nbsp;*<br />
&nbsp;* Notes : uses call_user_func_array() so passing parameters doesn't depend on $fun signature<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; It also returns FALSE upon error.<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Please check the php documentation for more information<br />
&nbsp;*/<br />
</span><span class="keyword">function </span><span class="default">map</span><span class="keyword">(</span><span class="default">$fun</span><span class="keyword">, </span><span class="default">$list</span><span class="keyword">,</span><span class="default">$params</span><span class="keyword">=array()){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$acc</span><span class="keyword">=</span><span class="default">NULL</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$last</span><span class="keyword">=</span><span class="default">array_push</span><span class="keyword">(</span><span class="default">$params</span><span class="keyword">, </span><span class="default">NULL</span><span class="keyword">,</span><span class="default">$acc</span><span class="keyword">)-</span><span class="default">1</span><span class="keyword">; </span><span class="comment">// alloc $element and $acc at the end<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$list </span><span class="keyword">as </span><span class="default">$params</span><span class="keyword">[</span><span class="default">$last</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">]){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$params</span><span class="keyword">[</span><span class="default">$last</span><span class="keyword">]=</span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$fun </span><span class="keyword">, </span><span class="default">$params&nbsp; </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$acc</span><span class="keyword">=</span><span class="default">array_pop</span><span class="keyword">(</span><span class="default">$params</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$acc</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">add</span><span class="keyword">(</span><span class="default">$element</span><span class="keyword">,</span><span class="default">$acc</span><span class="keyword">){ </span><span class="comment">// maybe only with multi-length function<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$acc </span><span class="keyword">== </span><span class="default">NULL</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$acc</span><span class="keyword">=</span><span class="default">$element</span><span class="keyword">+</span><span class="default">$acc</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">addTo</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">addTo</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">);<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">addTo</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">);<br />
echo </span><span class="string">"result = $result\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">$result</span><span class="keyword">=</span><span class="default">map</span><span class="keyword">(</span><span class="string">'addTo'</span><span class="keyword">,array(</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">));<br />
echo </span><span class="string">"result= $result\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="60984"></a>
 <div class="note">
  <strong class='user'>boards at gmail dot com</strong>
  <a href="#60984" class="date">22-Jan-2006 10:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to call a static function (PHP5) in a variable method:<br />
<br />
Make an array of two entries where the 0th entry is the name of the class to be invoked ('self' and 'parent' work as well) and the 1st entry is the name of the function.&nbsp; Basically, a 'callback' variable is either a string (the name of the function) or an array (0 =&gt; 'className', 1 =&gt; 'functionName').<br />
<br />
Then, to call that function, you can use either call_user_func() or call_user_func_array().&nbsp; Examples:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
<br />
&nbsp; protected </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp; protected </span><span class="default">$c</span><span class="keyword">;<br />
<br />
&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">= array(</span><span class="string">'self'</span><span class="keyword">, </span><span class="string">'a'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c </span><span class="keyword">= array(</span><span class="string">'self'</span><span class="keyword">, </span><span class="string">'c'</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; static function </span><span class="default">a</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, &amp;</span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$name</span><span class="keyword">,</span><span class="string">' =&gt; '</span><span class="keyword">,</span><span class="default">$value</span><span class="keyword">++,</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">b</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, &amp;</span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a</span><span class="keyword">, array(</span><span class="default">$name</span><span class="keyword">, &amp;</span><span class="default">$value</span><span class="keyword">));<br />
&nbsp; }<br />
<br />
&nbsp; static function </span><span class="default">c</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$str</span><span class="keyword">,</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">d</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c</span><span class="keyword">, </span><span class="default">func_get_args</span><span class="keyword">());<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">e</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c</span><span class="keyword">, </span><span class="default">func_get_arg</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">));<br />
&nbsp; }<br />
<br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
<br />
&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">= array(</span><span class="string">'parent'</span><span class="keyword">, </span><span class="string">'a'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c </span><span class="keyword">= array(</span><span class="string">'self'</span><span class="keyword">, </span><span class="string">'c'</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; static function </span><span class="default">c</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">func_get_args</span><span class="keyword">());<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">d</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c</span><span class="keyword">, </span><span class="default">func_get_args</span><span class="keyword">());<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">e</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c</span><span class="keyword">, </span><span class="default">func_get_args</span><span class="keyword">());<br />
&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">=&amp; new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; new </span><span class="default">B</span><span class="keyword">;<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
</span><span class="default">A</span><span class="keyword">::</span><span class="default">a</span><span class="keyword">(</span><span class="string">'index'</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">);<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">b</span><span class="keyword">(</span><span class="string">'index'</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">);<br />
<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">c</span><span class="keyword">(</span><span class="string">'string'</span><span class="keyword">);<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">d</span><span class="keyword">(</span><span class="string">'string'</span><span class="keyword">);<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">e</span><span class="keyword">(</span><span class="string">'string'</span><span class="keyword">);<br />
<br />
</span><span class="comment"># etc.<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="52491"></a>
 <div class="note">
  <strong class='user'>Storm</strong>
  <a href="#52491" class="date">03-May-2005 08:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This can quite useful for a dynamic database class:<br />
<br />
(Note: This just a simplified section)<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">db </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$host </span><span class="keyword">= </span><span class="string">'localhost'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$user </span><span class="keyword">= </span><span class="string">'username'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$pass </span><span class="keyword">= </span><span class="string">'password'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'mysqli'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$lid </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Connection function<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">connect</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$connect </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">type</span><span class="keyword">.</span><span class="string">'_connect'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">lid </span><span class="keyword">= </span><span class="default">$connect</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">host</span><span class="keyword">, </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user</span><span class="keyword">, </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">pass</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">'Unable to connect.'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;}<br />
}<br />
</span><span class="default">$db&nbsp; </span><span class="keyword">= new </span><span class="default">db</span><span class="keyword">;<br />
</span><span class="default">$db</span><span class="keyword">-&gt;</span><span class="default">connect</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Much easier than having multiple database classes or even extending a base class.</span>
</code></div>
  </div>
 </div>
 <a name="27839"></a>
 <div class="note">
  <strong class='user'>ian at NO_SPAM dot verteron dot net</strong>
  <a href="#27839" class="date">20-Dec-2002 07:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A good method to pass around variables containing function names within some class is to use the same method as the developers use in preg_replace_callback - with arrays containing an instance of the class and the function name itself.<br />
<br />
function call_within_an_object($fun)<br />
{<br />
&nbsp; if(is_array($fun))<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; /* call a function within an object */<br />
&nbsp;&nbsp;&nbsp; $fun[0]-&gt;{$fun[1]}();<br />
&nbsp; }<br />
&nbsp; else<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; /* call some other function */<br />
&nbsp;&nbsp;&nbsp; $fun();<br />
&nbsp; }<br />
}<br />
<br />
function some_other_fun()<br />
{<br />
&nbsp; /* code */<br />
}<br />
<br />
class x<br />
{<br />
&nbsp; function fun($value)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; /* some code */<br />
&nbsp; }<br />
}<br />
<br />
$x = new x();<br />
<br />
/* the following line calls $x-&gt;fun() */<br />
call_within_an_object(Array($x, 'fun'));<br />
<br />
/* the following line calls some_other_fun() */<br />
call_within_an_object('some_other_fun');</span>
</code></div>
  </div>
 </div>
 <a name="24931"></a>
 <div class="note">
  <strong class='user'>madeinlisboa at yahoo dot com</strong>
  <a href="#24931" class="date">05-Sep-2002 05:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Finally, a very easy way to call a variable method in a class:<br />
<br />
Example of a class:<br />
<br />
class Print() {<br />
&nbsp;&nbsp;&nbsp; var $mPrintFunction;<br />
<br />
&nbsp;&nbsp;&nbsp; function Print($where_to) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;mPrintFunction = "PrintTo$where_to";<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function PrintToScreen($content) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $content;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function PrintToFile($content) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; fputs ($file, $contents);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
.. .. ..<br />
<br />
&nbsp;&nbsp;&nbsp; // first, function name is parsed, then function is called<br />
&nbsp;&nbsp;&nbsp; $this-&gt;{$this-&gt;mPrintFunction}("something to print");<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="21199"></a>
 <div class="note">
  <strong class='user'>msmith at pmcc dot com</strong>
  <a href="#21199" class="date">02-May-2002 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Try the call_user_func() function.&nbsp; I find it's a bit simpler to implement, and at very least makes your code a bit more readable... much more readable and simpler to research for someone who isn't familiar with this construct.</span>
</code></div>
  </div>
 </div>
 <a name="19979"></a>
 <div class="note">
  <strong class='user'>anpatel at NOSPAM_cbu dot edu</strong>
  <a href="#19979" class="date">17-Mar-2002 09:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Yes interpolation can be very tricky. I suggest that you always use parenthesis, or curly brackets(whichever applies) to make your expression clear.<br />
<br />
Dont ever depend on a language's expression parse preference order.</span>
</code></div>
  </div>
 </div>
 <a name="18189"></a>
 <div class="note">
  <strong class='user'>retro at enx dot org</strong>
  <a href="#18189" class="date">13-Jan-2002 07:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another way to have php parse a variable within an object as a function is to simply set a temporary variable to its value. For example:<br />
<br />
$obj-&gt;myfunction = "foo";<br />
$x = $obj-&gt;myfunction;<br />
$x(); // calls the function named "foo"</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=functions.variable-functions&amp;redirect=@w{RN23NHC3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.variable-functions&amp;redirect=@w{RN23NHC3}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/functions.variable-functions.php">show source</a> |
 <a href="/credits.php">credits</a> |
 <a href="/stats/">stats</a> |
 <a href="/sitemap.php">sitemap</a> |
 <a href="/contact.php">contact</a> |
 <a href="/contact.php#ads">advertising</a> |
 <a href="/mirrors.php">mirror sites</a>
</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="/copyright.php">Copyright &copy; 2001-2012 The PHP Group</a><br />
  All rights reserved.
 </div>

 <div id="thismirror">
  <a href="/mirror.php">This mirror</a> generously provided by:
  <a href="@w{TDAY9QJ9}">Yahoo! Inc.</a><br />
  Last updated: Tue Jul 31 20:41:05 2012 UTC
 </div>
</div>
<!--[if IE 6]>
<script type="text/javascript">
    /*Load jQuery if not already loaded*/ if(typeof jQuery == 'undefined'){ document.write("<script type=\"text/javascript\"   src=\"@w{8JFFCNVW}"></"+"script>"); var __noconflict = true; }
    var IE6UPDATE_OPTIONS = {
        icons_path: "/ie6update/images/"
    }
</script>
<script type="text/javascript" src="/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>