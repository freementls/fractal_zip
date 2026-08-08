<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_POST - Manual</title>
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
 <link rel="index" href="reserved.variables.php" />
 <link rel="prev" href="reserved.variables.get.php" />
 <link rel="next" href="reserved.variables.files.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.post" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.post.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/reserved.variables.post.php" />
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
 <li class="header up"><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="language.variables.superglobals.php">Superglobals</a></li>
 <li><a href="reserved.variables.globals.php">$GLOBALS</a></li>
 <li><a href="reserved.variables.server.php">$_SERVER</a></li>
 <li><a href="reserved.variables.get.php">$_GET</a></li>
 <li class="active"><a href="reserved.variables.post.php">$_POST</a></li>
 <li><a href="reserved.variables.files.php">$_FILES</a></li>
 <li><a href="reserved.variables.request.php">$_REQUEST</a></li>
 <li><a href="reserved.variables.session.php">$_SESSION</a></li>
 <li><a href="reserved.variables.environment.php">$_ENV</a></li>
 <li><a href="reserved.variables.cookies.php">$_COOKIE</a></li>
 <li><a href="reserved.variables.phperrormsg.php">$php_errormsg</a></li>
 <li><a href="reserved.variables.httprawpostdata.php">$HTTP_RAW_POST_DATA</a></li>
 <li><a href="reserved.variables.httpresponseheader.php">$http_response_header</a></li>
 <li><a href="reserved.variables.argc.php">$argc</a></li>
 <li><a href="reserved.variables.argv.php">$argv</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="reserved.variables.files.php">$_FILES<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.get.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_GET</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.post.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.post.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.post.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.post.php">French</option>
    <option value="de/reserved.variables.post.php">German</option>
    <option value="ja/reserved.variables.post.php">Japanese</option>
    <option value="pl/reserved.variables.post.php">Polish</option>
    <option value="ro/reserved.variables.post.php">Romanian</option>
    <option value="ru/reserved.variables.post.php">Russian</option>
    <option value="fa/reserved.variables.post.php">Persian</option>
    <option value="es/reserved.variables.post.php">Spanish</option>
    <option value="tr/reserved.variables.post.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.post" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_POST</h1>
  <h1 class="refname">$HTTP_POST_VARS [deprecated]</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_POST</span> -- <span class="refname">$HTTP_POST_VARS [deprecated]</span> &mdash; <span class="dc-title">HTTP POST variables</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.post-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative array of variables passed to the current script
   via the HTTP POST method. 
  </p>

  <p class="simpara">
   <var class="varname"><var class="varname">$HTTP_POST_VARS</var></var> contains the same initial
   information, but is not a <a href="language.variables.superglobals.php" class="link">superglobal</a>.
   (Note that <var class="varname"><var class="varname">$HTTP_POST_VARS</var></var> and <var class="varname"><var class="varname">$_POST</var></var>
   are different variables and that PHP handles them as such)
  </p>

 </div>

 

 <div class="refsect1 changelog" id="refsect1-reserved.variables.post-changelog">
  <h3 class="title">Changelog</h3>
  <p class="para">
   <table class="doctable informaltable">
    
     <thead>
      <tr>
       <th>Version</th>
       <th>Description</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>4.1.0</td>
       <td>
        Introduced <var class="varname"><var class="varname">$_POST</var></var> that deprecated
        <var class="varname"><var class="varname">$HTTP_POST_VARS</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-reserved.variables.post-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="variable.post.basic">
    <p><strong>Example #1 <var class="varname"><var class="varname">$_POST</var></var> example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">htmlspecialchars</span><span style="color: #007700">(</span><span style="color: #0000BB">$_POST</span><span style="color: #007700">[</span><span style="color: #DD0000">"name"</span><span style="color: #007700">])&nbsp;.&nbsp;</span><span style="color: #DD0000">'!'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>
     Assuming the user POSTed name=Hannes
    </p></div>
    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Hello Hannes!
</pre></div>
    </div>
   </div>
  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.post-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-reserved.variables.post-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"><a href="language.variables.external.php" class="link">Handling external variables</a></li>
    <li class="member"><a href="book.filter.php" class="link">The filter extension</a></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.files.php">$_FILES<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.get.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_GET</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.post.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.post&amp;redirect=http://www.php.net/manual/en/reserved.variables.post.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.post&amp;redirect=http://www.php.net/manual/en/reserved.variables.post.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_POST</strong>
 </div><div id="allnotes">
 <a name="104119"></a>
 <div class="note">
  <strong class='user'>perdondiego at mailinator dot com</strong>
  <a href="#104119" class="date">24-May-2011 01:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to "php dot net at bigbadaboom dot net", adding the value attr to the image submit button may not work in older browser (Opera 9.x for example).<br />
<br />
A better solution would be to add a hidden input (&lt;input name="hidden" .... /&gt; in the form to handle both cases: when we have a submit button or an image button for submitting</span>
</code></div>
  </div>
 </div>
 <a name="96902"></a>
 <div class="note">
  <strong class='user'>yesk13 at gmail dot com</strong>
  <a href="#96902" class="date">21-Mar-2010 11:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
you may have multidimensional array in form inputs<br />
<br />
HTML Example:<br />
<br />
&lt;input name="data[User][firstname]" type="text" /&gt;<br />
&lt;input name="data[User][lastname]" type="text" /&gt;<br />
...<br />
<br />
Inside php script<br />
after submit you can access the individual element like so:<br />
<br />
$firstname = $_POST['data']['User']['firstname'];<br />
...</span>
</code></div>
  </div>
 </div>
 <a name="95938"></a>
 <div class="note">
  <strong class='user'>toader_alexandru at yahoo dot com</strong>
  <a href="#95938" class="date">29-Jan-2010 07:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you have a form with some radio:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">&lt;</span><span class="default">form method</span><span class="keyword">=</span><span class="string">'post'</span><span class="keyword">&gt;<br />
&lt;</span><span class="default">input type</span><span class="keyword">=</span><span class="string">'radio' </span><span class="default">name</span><span class="keyword">=</span><span class="string">'55' </span><span class="default">value</span><span class="keyword">=</span><span class="string">'1' </span><span class="keyword">&gt;<br />
&lt;</span><span class="default">input type</span><span class="keyword">=</span><span class="string">'radio' </span><span class="default">name</span><span class="keyword">=</span><span class="string">'55' </span><span class="default">value</span><span class="keyword">=</span><span class="string">'2' </span><span class="keyword">&gt;<br />
&lt;</span><span class="default">input type</span><span class="keyword">=</span><span class="string">'radio' </span><span class="default">name</span><span class="keyword">=</span><span class="string">'55' </span><span class="default">value</span><span class="keyword">=</span><span class="string">'3' </span><span class="keyword">&gt;<br />
&lt;</span><span class="default">input type</span><span class="keyword">=</span><span class="string">'radio' </span><span class="default">name</span><span class="keyword">=</span><span class="string">'55' </span><span class="default">value</span><span class="keyword">=</span><span class="string">'4' </span><span class="keyword">&gt;<br />
<br />
&lt;</span><span class="default">input type</span><span class="keyword">=</span><span class="string">'submit' </span><span class="default">value</span><span class="keyword">=</span><span class="string">'ok' </span><span class="keyword">&gt;<br />
&lt;/</span><span class="default">form</span><span class="keyword">&gt;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
if the name of the radio is a number will be returned by $_POST as INT!!!! not string as the manual says<br />
<br />
if the name of the radio is 'test' will be of course returned ad string.<br />
<br />
So your post array will look like this:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">array<br />
</span><span class="default">167302 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'167308' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">6</span><span class="keyword">)<br />
</span><span class="default">167314 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'167344' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">6</span><span class="keyword">)<br />
</span><span class="default">167347 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'5867521' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">7</span><span class="keyword">)<br />
</span><span class="string">'3754952706' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3754952706' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3744466946' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3744466946' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3740271617' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3740271617' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3749709826' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3749709826' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3749708801' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'1' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
</span><span class="string">'meta' </span><span class="keyword">=&gt;<br />
array<br />
</span><span class="string">'hasJavascript' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'0' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
</span><span class="string">'timeToComplete' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'1264769572.6144' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">15</span><span class="keyword">)<br />
</span><span class="string">'Submit' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'Next' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">4</span><span class="keyword">)<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Note the first 3 keys are int not string<br />
<br />
This could affect you if you do some operations for example if you do an array_merge with your post values:<br />
<br />
<span class="default">&lt;?php<br />
$arr1 </span><span class="keyword">= array(<br />
</span><span class="default">167302 </span><span class="keyword">=&gt; </span><span class="string">'167308'</span><span class="keyword">,<br />
</span><span class="default">167314 </span><span class="keyword">=&gt; </span><span class="string">'167344'</span><span class="keyword">,<br />
</span><span class="default">167347 </span><span class="keyword">=&gt; </span><span class="string">'5867521'</span><span class="keyword">,<br />
</span><span class="string">'3754952706' </span><span class="keyword">=&gt; </span><span class="string">'3754952706'</span><span class="keyword">,<br />
</span><span class="string">'3744466946' </span><span class="keyword">=&gt; </span><span class="string">'3744466946'</span><span class="keyword">,<br />
</span><span class="string">'3740271617' </span><span class="keyword">=&gt; </span><span class="string">'3740271617'</span><span class="keyword">,<br />
</span><span class="string">'3749709826' </span><span class="keyword">=&gt; </span><span class="string">'3749709826'</span><span class="keyword">,<br />
</span><span class="string">'3749708801' </span><span class="keyword">=&gt; </span><span class="string">'1'<br />
</span><span class="keyword">);<br />
<br />
</span><span class="default">$arr2 </span><span class="keyword">= array(<br />
</span><span class="string">'meta' </span><span class="keyword">=&gt;<br />
array (<br />
</span><span class="string">'hasJavascript' </span><span class="keyword">=&gt; </span><span class="string">'0'</span><span class="keyword">,<br />
</span><span class="string">'timeToComplete' </span><span class="keyword">=&gt; </span><span class="string">'1264769572.6144'<br />
</span><span class="keyword">)<br />
);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
if you do :<br />
<span class="default">&lt;?php <br />
$values </span><span class="keyword">= </span><span class="default">array_merge</span><span class="keyword">(</span><span class="default">$arr1</span><span class="keyword">, </span><span class="default">$arr2</span><span class="keyword">);</span><span class="comment">//this is done by zend in $form-&gt;getValues()<br />
</span><span class="default">?&gt;<br />
</span><br />
guess what will be the result? your numeric keys will be reindexed starting from 0.<br />
<br />
and your post values will look like this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
0 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'167308' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">6</span><span class="keyword">)<br />
</span><span class="default">1 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'167344' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">6</span><span class="keyword">)<br />
</span><span class="default">2 </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'5867521' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">7</span><span class="keyword">)<br />
</span><span class="string">'3754952706' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3754952706' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3744466946' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3744466946' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3740271617' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3740271617' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3749709826' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'3749709826' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">)<br />
</span><span class="string">'3749708801' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'1' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
</span><span class="string">'meta' </span><span class="keyword">=&gt;<br />
array<br />
</span><span class="string">'hasJavascript' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'0' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
</span><span class="string">'timeToComplete' </span><span class="keyword">=&gt; </span><span class="default">string </span><span class="string">'1264769572.6144' </span><span class="keyword">(</span><span class="default">length</span><span class="keyword">=</span><span class="default">15</span><span class="keyword">)<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="89828"></a>
 <div class="note">
  <strong class='user'>eddyvlad at eddyvlad dot com</strong>
  <a href="#89828" class="date">24-Mar-2009 07:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Take note that all $_POST values are (string).<br />
If your form field should accept both numeric and alphabets but exclude a value of 0, you will have a problem.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">## Using Equal operator<br />
</span><span class="keyword">if(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">] == </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'You cannot set it to 0'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'Correct'</span><span class="keyword">;<br />
}<br />
</span><span class="comment"># You will get the following results<br />
<br />
// $_POST['myfield'] = 0;<br />
// Output: You cannot set it to 0;<br />
<br />
// $_POST['myfield'] = 1;<br />
// Output: Correct;<br />
<br />
// $_POST['myfield'] = 'test';<br />
// Output: You cannot set it to 0;<br />
<br />
## Using Identical operator<br />
</span><span class="keyword">if(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">] === </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'You cannot set it to 0'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'Correct'</span><span class="keyword">;<br />
}<br />
</span><span class="comment"># You will get the following results<br />
<br />
// $_POST['myfield'] = 0;<br />
// Output: Correct;<br />
<br />
// $_POST['myfield'] = 1;<br />
// Output: Correct;<br />
<br />
// $_POST['myfield'] = 'test';<br />
// Output: Correct;<br />
<br />
// You need to convert numeric values to integer and use identical operator<br />
</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">] = </span><span class="default">is_numeric</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">]) ? (int)</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">] : </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'alias'</span><span class="keyword">];<br />
if(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'myfield'</span><span class="keyword">] === </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'You cannot set it to 0'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'Correct'</span><span class="keyword">;<br />
}<br />
</span><span class="comment"># Now you will get the following results<br />
<br />
// $_POST['myfield'] = 0;<br />
// Output: You cannot set it to 0;<br />
<br />
// $_POST['myfield'] = 1;<br />
// Output: Correct;<br />
<br />
// $_POST['myfield'] = 'test';<br />
// Output: Correct;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="88433"></a>
 <div class="note">
  <strong class='user'>initself</strong>
  <a href="#88433" class="date">23-Jan-2009 11:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
# This will convert $_POST into a query string<br />
<br />
<span class="default">&lt;?php<br />
$query_string </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
if (</span><span class="default">$_POST</span><span class="keyword">) {<br />
&nbsp; </span><span class="default">$kv </span><span class="keyword">= array();<br />
&nbsp; foreach (</span><span class="default">$_POST </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$kv</span><span class="keyword">[] = </span><span class="string">"$key=$value"</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; </span><span class="default">$query_string </span><span class="keyword">= </span><span class="default">join</span><span class="keyword">(</span><span class="string">"&amp;"</span><span class="keyword">, </span><span class="default">$kv</span><span class="keyword">);<br />
}<br />
else {<br />
&nbsp; </span><span class="default">$query_string </span><span class="keyword">= </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'QUERY_STRING'</span><span class="keyword">];<br />
}<br />
echo </span><span class="default">$query_string</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="87650"></a>
 <div class="note">
  <strong class='user'>james dot ellis at gmail dot com</strong>
  <a href="#87650" class="date">14-Dec-2008 05:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One feature of PHP's processing of POST and GET variables is that it automatically decodes indexed form variable names.<br />
<br />
I've seem innumerable projects that jump through extra &amp; un-needed processing hoops to decode variables when PHP does it all for you:<br />
<br />
Example pseudo code:<br />
<br />
Many web sites do this:<br />
<br />
&lt;form ....&gt;<br />
&lt;input name="person_0_first_name" value="john" /&gt;<br />
&lt;input name="person_0_last_name" value="smith" /&gt;<br />
...<br />
<br />
&lt;input name="person_1_first_name" value="jane" /&gt;<br />
&lt;input name="person_1_last_name" value="jones" /&gt;<br />
&lt;/form&gt;<br />
<br />
When they could do this:<br />
<br />
&lt;form ....&gt;<br />
&lt;input name="person[0][first_name]" value="john" /&gt;<br />
&lt;input name="person[0][last_name]" value="smith" /&gt;<br />
...<br />
&lt;input name="person[1][first_name]" value="jane" /&gt;<br />
&lt;input name="person[1][last_name]" value="jones" /&gt;<br />
&lt;/form&gt;<br />
<br />
With the first example you'd have to do string parsing / regexes to get the correct values out so they can be married with other data in your app... whereas with the second example.. you will end up with something like:<br />
<span class="default">&lt;?php<br />
var_dump</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'person'</span><span class="keyword">]);<br />
</span><span class="comment">//will get you something like:<br />
</span><span class="keyword">array (<br />
</span><span class="default">0 </span><span class="keyword">=&gt; array(</span><span class="string">'first_name'</span><span class="keyword">=&gt;</span><span class="string">'john'</span><span class="keyword">,</span><span class="string">'last_name'</span><span class="keyword">=&gt;</span><span class="string">'smith'</span><span class="keyword">),<br />
</span><span class="default">1 </span><span class="keyword">=&gt; array(</span><span class="string">'first_name'</span><span class="keyword">=&gt;</span><span class="string">'jane'</span><span class="keyword">,</span><span class="string">'last_name'</span><span class="keyword">=&gt;</span><span class="string">'jones'</span><span class="keyword">),<br />
)<br />
</span><span class="default">?&gt;<br />
</span><br />
This is invaluable when you want to link various posted form data to other hashes on the server side, when you need to store posted data in separate "compartment" arrays or when you want to link your POSTed data into different record handlers in various Frameworks.<br />
<br />
Remember also that using [] as in index will cause a sequential numeric array to be created once the data is posted, so sometimes it's better to define your indexes explicitly.</span>
</code></div>
  </div>
 </div>
 <a name="87512"></a>
 <div class="note">
  <strong class='user'>paul at youngish dot homelinux^org</strong>
  <a href="#87512" class="date">08-Dec-2008 01:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For a page with multiple forms here is one way of processing the different POST values that you may receive.&nbsp; This code is good for when you have distinct forms on a page.&nbsp; Adding another form only requires an extra entry in the array and switch statements. <br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;</span><span class="keyword">if (!empty(</span><span class="default">$_POST</span><span class="keyword">))<br />
&nbsp;{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Array of post values for each different form on your page.<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$postNameArr </span><span class="keyword">= array(</span><span class="string">'F1_Submit'</span><span class="keyword">, </span><span class="string">'F2_Submit'</span><span class="keyword">, </span><span class="string">'F3_Submit'</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; <br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Find all of the post identifiers within $_POST<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$postIdentifierArr </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$postNameArr </span><span class="keyword">as </span><span class="default">$postName</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$postName</span><span class="keyword">, </span><span class="default">$_POST</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$postIdentifierArr</span><span class="keyword">[] = </span><span class="default">$postName</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Only one form should be submitted at a time so we should have one<br />
&nbsp;&nbsp;&nbsp; // post identifier.&nbsp; The die statements here are pretty harsh you may consider<br />
&nbsp;&nbsp;&nbsp; // a warning rather than this. <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">count</span><span class="keyword">(</span><span class="default">$postIdentifierArr</span><span class="keyword">) != </span><span class="default">1</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">count</span><span class="keyword">(</span><span class="default">$postIdentifierArr</span><span class="keyword">) &lt; </span><span class="default">1 </span><span class="keyword">or<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">"\$_POST contained more than one post identifier: " </span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">implode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">, </span><span class="default">$postIdentifierArr</span><span class="keyword">));<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// We have not died yet so we must have less than one.<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">die(</span><span class="string">"\$_POST did not contain a known post identifier."</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; switch (</span><span class="default">$postIdentifierArr</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">])<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; case </span><span class="string">'F1_Submit'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="string">"Perform actual code for F1_Submit."</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; break;<br />
<br />
&nbsp;&nbsp;&nbsp; case </span><span class="string">'Modify'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="string">"Perform actual code for F2_Submit."</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; case </span><span class="string">'Delete'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="string">"Perform actual code for F3_Submit."</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; break;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
else </span><span class="comment">// $_POST is empty.<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"Perform code for page without POST data. "</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84813"></a>
 <div class="note">
  <strong class='user'>jairhumberto at gmail dot com</strong>
  <a href="#84813" class="date">30-Jul-2008 06:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">foreach(</span><span class="default">$_POST </span><span class="keyword">as </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$v</span><span class="keyword">) $</span><span class="default">$k</span><span class="keyword">=</span><span class="default">$v</span><span class="keyword">;<br />
</span><span class="comment">//to use $_POST["example"] as $example<br />
<br />
</span><span class="keyword">foreach(</span><span class="default">$_GET </span><span class="keyword">as </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$v</span><span class="keyword">) $</span><span class="default">$k</span><span class="keyword">=</span><span class="default">$v</span><span class="keyword">;<br />
</span><span class="comment">//to use $_GET["example"] as $example<br />
<br />
//or better:<br />
<br />
</span><span class="keyword">foreach(${</span><span class="string">"_" </span><span class="keyword">. </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">"REQUEST_METHOD"</span><span class="keyword">]} as </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$v</span><span class="keyword">) $</span><span class="default">$k</span><span class="keyword">=</span><span class="default">$v</span><span class="keyword">;<br />
</span><span class="comment">//to use $_GET["example"] or $_POST["example"] as $example<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84457"></a>
 <div class="note">
  <strong class='user'>php dot net at bigbadaboom dot net</strong>
  <a href="#84457" class="date">15-Jul-2008 01:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Make sure your submit buttons (ie. &lt;input type="submit"&gt; etc) have a 'value' attribute.&nbsp; If they don't, the value won't appear in $_POST and so isset($_POST["submit"]) won't work either.<br />
<br />
Example:<br />
<br />
&lt;input type="submit" name="submit"&gt;<br />
<br />
isset($_POST["submit"]) returns false<br />
<br />
&lt;input type="submit" name="submit" value="Next"&gt;<br />
<br />
isset($_POST["submit"]) returns true.<br />
<br />
This might seem obvious for text buttons since they need a label anyway.&nbsp; However, if you are using image buttons, it might not occur to you that you need to set a value attribute as well.&nbsp; For example, the value attribute is required in the following element if you want to be able to detect it in your script.<br />
<br />
&lt;input type="image" name="submit" src="next.gif" value="Next"&gt;</span>
</code></div>
  </div>
 </div>
 <a name="83949"></a>
 <div class="note">
  <strong class='user'>paul dot chubb at abs dot gov dot au</strong>
  <a href="#83949" class="date">19-Jun-2008 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Nasty bug in IE6, Apache2 and mod_auth_sspi. Essentially if the user presses the submit button too quickly, $_POST (and the equivalents) comes back empty. The workaround is to set Apache's KeepAliveTimeout to 1. This would mean that the user would need to push submit within a second to trigger the issue.</span>
</code></div>
  </div>
 </div>
 <a name="83300"></a>
 <div class="note">
  <strong class='user'>telconstar99 at hotnospampleasemail dot com</strong>
  <a href="#83300" class="date">19-May-2008 05:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
&lt;?<br />
//If we submitted the form<br />
if(isset($_POST['submitMe']))<br />
{<br />
&nbsp;&nbsp; &nbsp; echo("Hello, " . $_POST['name'] . ", we submitted your form!");<br />
}<br />
//If we haven't submitted the form<br />
else<br />
{<br />
?&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;form action="&lt;?=$_SERVER['PHP_SELF']?&gt;" method="POST"&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;input type="text" name="name"&gt;&lt;br&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;input type="submit" value="submit" name="submitMe"&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;/form&gt;<br />
&lt;?<br />
}<br />
?&gt;</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.post&amp;redirect=http://www.php.net/manual/en/reserved.variables.post.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.post&amp;redirect=http://www.php.net/manual/en/reserved.variables.post.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.post.php">show source</a> |
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