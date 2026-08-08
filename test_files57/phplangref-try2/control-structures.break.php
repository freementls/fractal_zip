<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: break - Manual</title>
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
 <link rel="index" href="language.control-structures.php" />
 <link rel="prev" href="control-structures.foreach.php" />
 <link rel="next" href="control-structures.continue.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/break" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.break.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.break.php" />
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
 <li class="header up"><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="control-structures.intro.php">Introduction</a></li>
 <li><a href="control-structures.if.php">if</a></li>
 <li><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li class="active"><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li><a href="control-structures.declare.php">declare</a></li>
 <li><a href="function.return.php">return</a></li>
 <li><a href="function.require.php">require</a></li>
 <li><a href="function.include.php">include</a></li>
 <li><a href="function.require-once.php">require_<span class="w"> </span>once</a></li>
 <li><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="control-structures.continue.php">continue<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.foreach.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />foreach</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.break.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.break.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.break.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.break.php">French</option>
    <option value="de/control-structures.break.php">German</option>
    <option value="ja/control-structures.break.php">Japanese</option>
    <option value="pl/control-structures.break.php">Polish</option>
    <option value="ro/control-structures.break.php">Romanian</option>
    <option value="ru/control-structures.break.php">Russian</option>
    <option value="fa/control-structures.break.php">Persian</option>
    <option value="es/control-structures.break.php">Spanish</option>
    <option value="tr/control-structures.break.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.break" class="sect1">
 <h2 class="title"><em>break</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="simpara">
  <em>break</em> ends execution of the current
  <em>for</em>, <em>foreach</em>,
  <em>while</em>, <em>do-while</em> or
  <em>switch</em> structure.
 </p>
 <p class="simpara">
  <em>break</em> accepts an optional numeric argument
  which tells it how many nested enclosing structures are to be
  broken out of.
 </p>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$arr&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'one'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'two'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'three'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'four'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'stop'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'five'</span><span style="color: #007700">);<br />while&nbsp;(list(,&nbsp;</span><span style="color: #0000BB">$val</span><span style="color: #007700">)&nbsp;=&nbsp;</span><span style="color: #0000BB">each</span><span style="color: #007700">(</span><span style="color: #0000BB">$arr</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$val&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #DD0000">'stop'</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;You&nbsp;could&nbsp;also&nbsp;write&nbsp;'break&nbsp;1;'&nbsp;here.&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$val</span><span style="color: #DD0000">&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;Using&nbsp;the&nbsp;optional&nbsp;argument.&nbsp;*/<br /><br /></span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />while&nbsp;(++</span><span style="color: #0000BB">$i</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;switch&nbsp;(</span><span style="color: #0000BB">$i</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"At&nbsp;5&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;Exit&nbsp;only&nbsp;the&nbsp;switch.&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">case&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"At&nbsp;10;&nbsp;quitting&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;Exit&nbsp;the&nbsp;switch&nbsp;and&nbsp;the&nbsp;while.&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">default:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="para">
  <table class="doctable table">
   <caption><strong>Changelog for <em>break</em></strong></caption>
   
    <thead>
     <tr>
      <th>Version</th>
      <th>Description</th>
     </tr>

    </thead>

    <tbody class="tbody">
     <tr>
      <td>5.4.0</td>
      <td>
       <em>break 0;</em> is no longer valid. In previous versions it was interpreted
       the same as <em>break 1;</em>.
      </td>
     </tr>

     <tr>
      <td>5.4.0</td>
      <td>
       Removed the ability to pass in variables (e.g., <em>$num = 2; break $num;</em>)
       as the numerical argument.
      </td>
     </tr>

    </tbody>
   
  </table>

 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.continue.php">continue<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.foreach.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />foreach</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.break.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.break&amp;redirect=http://www.php.net/manual/en/control-structures.break.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.break&amp;redirect=http://www.php.net/manual/en/control-structures.break.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>break</strong>
 </div><div id="allnotes">
 <a name="109050"></a>
 <div class="note">
  <strong class='user'>steve at electricpocket dot com</strong>
  <a href="#109050" class="date">16-Jun-2012 12:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A break statement that is in the outer part of a program (e.g. not in a control loop) will end the script. This caught me out when I mistakenly had a break in an if statement<br />
<br />
i.e.<br />
<br />
<span class="default">&lt;?php <br />
</span><span class="keyword">echo </span><span class="string">"hello"</span><span class="keyword">;<br />
if (</span><span class="default">true</span><span class="keyword">) break;<br />
echo </span><span class="string">" world"</span><span class="keyword">; <br />
</span><span class="default">?&gt;<br />
</span><br />
will only show "hello"</span>
</code></div>
  </div>
 </div>
 <a name="101455"></a>
 <div class="note">
  <strong class='user'>RK</strong>
  <a href="#101455" class="date">17-Dec-2010 04:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If the numerical argument is higher than the number of things which can be broken out of, it seems to me like the execution of the entire program is stopped.<br />
My program had 8 nested loops. Didn't bother counting them, but wrote: break 10. - Result: Code following the loops was not processed.</span>
</code></div>
  </div>
 </div>
 <a name="90308"></a>
 <div class="note">
  <strong class='user'>Miguel Cruz</strong>
  <a href="#90308" class="date">15-Apr-2009 12:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To make it very clear, if you use "break" with no numerical argument, that's the same as doing "break 1".<br />
<br />
"break 0", while allowed, does nothing.</span>
</code></div>
  </div>
 </div>
 <a name="88783"></a>
 <div class="note">
  <strong class='user'>webmaster at kayfer dot com</strong>
  <a href="#88783" class="date">06-Feb-2009 10:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Ok guys here something that I use and it works<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">switch(</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">])<br />
<br />
{<br />
<br />
default:<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"yourvalue\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"yourvalue2\n"</span><span class="keyword">;<br />
break;<br />
<br />
case </span><span class="default">banner</span><span class="keyword">: <br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"content in banner\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"another content in banner\n"</span><span class="keyword">;<br />
break;<br />
<br />
case </span><span class="default">header</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"content in header\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"another content in header\n"</span><span class="keyword">;<br />
break;<br />
<br />
case </span><span class="default">other</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"content in other\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"another content in other\n"</span><span class="keyword">;<br />
break;<br />
<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now I will explain what each script does.<br />
<br />
first part the <br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">switch(</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">])<br />
<br />
{<br />
<br />
</span><span class="default">should be included </span><span class="keyword">or </span><span class="default">it won</span><span class="string">'t work<br />
<br />
default is default link, lets say if this is in index.php then when index.php is loaded default will be shown.<br />
<br />
case banner: or case header: or case other: is what will be hidden until visitors click on link that will take them there, the value after case is going to be added after the name of the file, lets say case banner: is in index.php then case banner: and its contents will be in index.php?id=banner same for header and other cases. note that you always should put break; tag should be included.<br />
<br />
Hope this helped you guys, I am sorry if i didn'</span><span class="default">t explain it well but I tried my best</span><span class="keyword">.</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85439"></a>
 <div class="note">
  <strong class='user'>Coryf88 at hotmail dot com</strong>
  <a href="#85439" class="date">30-Aug-2008 01:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Breaks are not required within a switch, they simply break the switch from being further processed.<br />
<span class="default">&lt;?php<br />
$total </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
switch(</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">6</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">= </span><span class="default">99</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">1</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">2</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">3</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">3</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">4</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">4</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="default">5</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">5</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Using the above snippet, all of the following would be true:<br />
If $i = 6, $total would = 99.<br />
If $i = 5, $total would = 5.<br />
If $i = 4, $total would = 9.<br />
If $i = 3, $total would = 12.<br />
If $i = 2, $total would = 14.<br />
If $i = 1, $total would = 15.</span>
</code></div>
  </div>
 </div>
 <a name="85083"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#85083" class="date">13-Aug-2008 09:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The previous note has a somewhat stupid author: why didn't you put an example?<br />
<br />
If I got it right, "case"s in "switch"es always need a "break".<br />
So this switch ...<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">switch ( </span><span class="default">$i </span><span class="keyword">) {<br />
&nbsp; case </span><span class="string">'1'</span><span class="keyword">: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"$i=1"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'2'</span><span class="keyword">: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include1.inc' </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'3'</span><span class="keyword">: </span><span class="comment">// Doesn't work<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include2.inc' </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'4'</span><span class="keyword">: </span><span class="comment">// Doesn't work, same reason<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include2.inc' </span><span class="keyword">);<br />
&nbsp; default: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
... has a problem in case "4" in that it doesn't have a "break" and this file...<br />
<br />
include2.inc:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
&nbsp; break;<br />
</span><span class="default">?&gt;<br />
</span><br />
... has a problem in that it has a "break" that is not in a "control structure" (like while, for...).<br />
<br />
In the same way, although "include2.inc" is "include"d (obvioulsy) in the <span class="default">&lt;?php ?&gt;</span> script (of main.php), it still need another <span class="default">&lt;?php ?&gt;</span> to wrap its content.<br />
<br />
Likewise,<br />
<br />
main.php<br />
<span class="default">&lt;?php <br />
</span><span class="keyword">echo </span><span class="string">"&lt;div&gt;This div is generated by php&lt;/div&gt;\n"</span><span class="keyword">;<br />
include </span><span class="string">'file.php'</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span>echo "&lt;div&gt;This div is also generated by php&lt;/div&gt;\n";<br />
<br />
file.php:<br />
&lt;div&gt;This is an old static HTML div.&lt;/div&gt;<br />
<br />
... doesn't require "file.php" content to start with "?&gt;" (exit php) and end with "&lt;?php" (go back to line 3 of main.php). This would FAIL.<br />
<br />
(See strange structure ALWAYS fail"?&gt;!)<br />
<br />
Does this really need documentation?</span>
</code></div>
  </div>
 </div>
 <a name="85025"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#85025" class="date">11-Aug-2008 11:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Your break doesn't work because it isn't within a control structure.&nbsp; I use includes that have breaks and they work fine because they are in a control structure (foreach, while, etc.)</span>
</code></div>
  </div>
 </div>
 <a name="84295"></a>
 <div class="note">
  <strong class='user'>cb</strong>
  <a href="#84295" class="date">08-Jul-2008 03:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just to note: break doesn't work within included file, results in fatal error.<br />
<br />
main.php:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">switch ( </span><span class="default">$i </span><span class="keyword">)<br />
{<br />
&nbsp; case </span><span class="string">'1'</span><span class="keyword">: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'2'</span><span class="keyword">: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include1.inc' </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'3'</span><span class="keyword">: </span><span class="comment">// Doesn't work<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include2.inc' </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; break;<br />
&nbsp; case </span><span class="string">'4'</span><span class="keyword">: </span><span class="comment">// Doesn't work, same reason<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">require( </span><span class="string">'include2.inc' </span><span class="keyword">);<br />
&nbsp; default: </span><span class="comment">// Works<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
include1.inc:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
include2.inc:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">echo </span><span class="string">"$i"</span><span class="keyword">;<br />
&nbsp; break;<br />
</span><span class="default">?&gt;<br />
</span><br />
I didn't find this documented anywhere.</span>
</code></div>
  </div>
 </div>
 <a name="82538"></a>
 <div class="note">
  <strong class='user'>alan at synergymx dot com</strong>
  <a href="#82538" class="date">15-Apr-2008 12:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is a function that returns specific files in an array, with all of the details. Includes some basic garbage checking.<br />
<br />
Variables<br />
<br />
$source_folder // the location of your files<br />
$ext // file extension you want to limit to (i.e.: *.txt)<br />
$sec // if you only want files that are at least so old.<br />
$limit // number of files you want to return<br />
<br />
The function<br />
<br />
function glob_files($source_folder, $ext, $sec, $limit){<br />
&nbsp;&nbsp;&nbsp; if( !is_dir( $source_folder ) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; die ( "Invalid directory.\n\n" );<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; $FILES = glob($source_folder."\*.".$ext);<br />
&nbsp;&nbsp;&nbsp; $set_limit&nbsp; &nbsp; = 0;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach($FILES as $key =&gt; $file) {<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( $set_limit == $limit )&nbsp; &nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( filemtime( $file ) &gt; $sec ){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $FILE_LIST[$key]['path']&nbsp; &nbsp; = substr( $file, 0, ( strrpos( $file, "\\" ) +1 ) );<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $FILE_LIST[$key]['name']&nbsp; &nbsp; = substr( $file, ( strrpos( $file, "\\" ) +1 ) );&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $FILE_LIST[$key]['size']&nbsp; &nbsp; = filesize( $file );<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $FILE_LIST[$key]['date']&nbsp; &nbsp; = date('Y-m-d G:i:s', filemtime( $file ) );<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $set_limit++;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; if(!empty($FILE_LIST)){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return $FILE_LIST;<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; die( "No files found!\n\n" );<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
So....<br />
<br />
$source_folder = "c:\temp\my_videos";<br />
$ext = "flv"; // flash video files<br />
$sec = "7200"; // files older than 2 hours<br />
$limit = 2; // Only get 2 files<br />
<br />
print_r(glob_files($source_folder, $ext, $sec, $limit));<br />
<br />
Would return:<br />
<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [path] =&gt; c:\temp\my_videos\<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; fluffy_bunnies.flv<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; 21160480<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [date] =&gt; 2007-10-30 16:48:05<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [path] =&gt; c:\temp\my_videos\<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; synergymx.com.flv<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; 14522744<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [date] =&gt; 2007-10-25 15:34:45<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )</span>
</code></div>
  </div>
 </div>
 <a name="77272"></a>
 <div class="note">
  <strong class='user'>Gautam</strong>
  <a href="#77272" class="date">22-Aug-2007 02:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
break :break command exits the innermost loop construct which contains it.<br />
break ends execution of the current for, foreach, while, do-while or switch structure. <br />
break accepts an optional numeric argument which tells it how many nested enclosing structures are to be broken out of. <br />
<br />
You can view various output by changing comparision operator(&lt;,==,&gt;) or value of $limit <br />
*/<br />
</span><span class="default">$to_square_root</span><span class="keyword">=</span><span class="default">65536</span><span class="keyword">;<br />
</span><span class="default">$i</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$limit</span><span class="keyword">=</span><span class="default">4</span><span class="keyword">;<br />
while (</span><span class="default">true</span><span class="keyword">) {<br />
</span><span class="default">$square_root</span><span class="keyword">=</span><span class="default">sqrt</span><span class="keyword">(</span><span class="default">$to_square_root</span><span class="keyword">);<br />
echo </span><span class="string">"Square Root of $to_square_root is $square_root.&lt;BR&gt;"</span><span class="keyword">;<br />
</span><span class="default">$to_square_root</span><span class="keyword">=</span><span class="default">$square_root</span><span class="keyword">;<br />
</span><span class="default">$i</span><span class="keyword">=</span><span class="default">$i</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">;<br />
if (</span><span class="default">$i</span><span class="keyword">&gt;</span><span class="default">$limit</span><span class="keyword">) </span><span class="comment">// if ($i&lt;$limit) is used, loop breaks on very first execution<br />
</span><span class="keyword">break;<br />
}<br />
</span><span class="default">$loop</span><span class="keyword">=</span><span class="default">$i</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">;<br />
echo </span><span class="string">"This loop is executes for $loop times."</span><span class="keyword">; <br />
</span><span class="comment">/* Above codes produces following output in browser <br />
Square Root of 65536 is 256<br />
Square Root of 256 is 16<br />
Square Root of 16 is 4<br />
Square Root of 4 is 2<br />
This loop is executes for 4 times <br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75936"></a>
 <div class="note">
  <strong class='user'>pinkgothic at gmail dot com</strong>
  <a href="#75936" class="date">22-Jun-2007 04:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To add to the responses given to "vlad at vlad dot neosurge dot net" - I'd like to note the lack of automatic breaking in 'default' can be a very good thing. Consider this useful snippet:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">switch((string) </span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'mode'</span><span class="keyword">]) {<br />
&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'mode'</span><span class="keyword">] = </span><span class="string">"search"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// fall through...<br />
&nbsp; </span><span class="keyword">case </span><span class="string">'search' </span><span class="keyword">:<br />
&nbsp; case </span><span class="string">'list' </span><span class="keyword">:<br />
&nbsp; case </span><span class="string">'add' </span><span class="keyword">:<br />
&nbsp; case </span><span class="string">'edit' </span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; require(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">).</span><span class="string">"/incs/"</span><span class="keyword">.</span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'mode'</span><span class="keyword">].</span><span class="string">".php"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; break;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I personally find that far easier to look at than, for example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$valid_modes </span><span class="keyword">= array(</span><span class="string">'search'</span><span class="keyword">,</span><span class="string">'list'</span><span class="keyword">,</span><span class="string">'add'</span><span class="keyword">,</span><span class="string">'edit'</span><span class="keyword">);<br />
if (</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'mode'</span><span class="keyword">],</span><span class="default">$valid_modes</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; require(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">).</span><span class="string">"/incs/"</span><span class="keyword">.</span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'mode'</span><span class="keyword">].</span><span class="string">".php"</span><span class="keyword">);<br />
} else {<br />
&nbsp;&nbsp; &nbsp; require(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">).</span><span class="string">"/incs/search.php"</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
...and it even has the added benefit that the switch() variant only has one require() statement, which makes for easier maintenance, e.g. if the directory changes, or what-have-you.<br />
<br />
(Consider the above pseudocode, please, it's not tested - it's code illustrating a point only.)</span>
</code></div>
  </div>
 </div>
 <a name="74940"></a>
 <div class="note">
  <strong class='user'>vinyanov at poczta dot onet dot pl</strong>
  <a href="#74940" class="date">05-May-2007 05:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the break argument accepts any expression, including a function result. So you may want to dynamically choose the loop level to break from:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// the print() function returns 1<br />
<br />
</span><span class="keyword">function </span><span class="default">icarus</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; while(print(</span><span class="string">'sea level, '</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while(print(</span><span class="string">'through the clouds, '</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; while(print(</span><span class="string">'close the Sun - '</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break </span><span class="default">rand</span><span class="keyword">(print(</span><span class="string">'FEATHERS LOSS! - '</span><span class="keyword">), </span><span class="default">3</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; print(</span><span class="string">'no feathers remaining.'</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">icarus</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="72429"></a>
 <div class="note">
  <a href="#72429" class="date">18-Jan-2007 06:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you wonder how to end execution of a function (as I did), it's that simple: return<br />
<br />
function foo($a) {<br />
&nbsp;if(!$a) return;<br />
&nbsp;echo 'true';<br />
&nbsp;// some other code<br />
}<br />
<br />
foo(true) will echo 'true', foo(false) won't echo anything (as return ends execution of the function. Of course, therefore there is no need for 'else' before 'echo').</span>
</code></div>
  </div>
 </div>
 <a name="71012"></a>
 <div class="note">
  <strong class='user'>clean_code at is_good_code dot com</strong>
  <a href="#71012" class="date">08-Nov-2006 12:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"Just an insignificant side not: Like in C/C++, it's not necessary to break out of the default part of a switch statement in PHP."<br />
<br />
--Yes it is, it's just that traditionally default: is the last entry of a switch and so nothing happens after.<br />
<br />
-If it was, for whatever reason, not the last entry the script would bawk, there is no implicit break; associated with switch.</span>
</code></div>
  </div>
 </div>
 <a name="60226"></a>
 <div class="note">
  <strong class='user'>traxer at gmx dot net</strong>
  <a href="#60226" class="date">30-Dec-2005 06:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
vlad at vlad dot neosurge dot net wrote on 04-Jan-2003 04:21<br />
<br />
&gt; Just an insignificant side not: Like in C/C++, it's not <br />
&gt; necessary to break out of the default part of a switch <br />
&gt; statement in PHP.<br />
<br />
It's not necessary to break out of any case of a switch&nbsp; statement in PHP, but if you want only one case to be executed, you have do break out of it (even out of the default case).<br />
<br />
Consider this:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'Apple'</span><span class="keyword">;<br />
switch (</span><span class="default">$a</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$a is not an orange&lt;br&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; case </span><span class="string">'Orange'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$a is an orange'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This prints (in PHP 5.0.4 on MS-Windows):<br />
$a is not an orange<br />
$a is an orange<br />
<br />
Note that the PHP documentation does not state the default part must be the last case statement.</span>
</code></div>
  </div>
 </div>
 <a name="50203"></a>
 <div class="note">
  <strong class='user'>Ilene Jones</strong>
  <a href="#50203" class="date">21-Feb-2005 01:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For Perl or C programmers...<br />
<br />
break is equivelant to last<br />
<br />
while(false ! == ($site = $d-&gt;read()) ) {<br />
&nbsp; if ($site === 'this') {<br />
&nbsp;&nbsp; &nbsp; break;&nbsp; // in perl this could be last;<br />
&nbsp; }<br />
}</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.break&amp;redirect=http://www.php.net/manual/en/control-structures.break.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.break&amp;redirect=http://www.php.net/manual/en/control-structures.break.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.break.php">show source</a> |
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