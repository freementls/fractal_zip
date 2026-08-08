<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_FILES - Manual</title>
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
 <link rel="prev" href="reserved.variables.post.php" />
 <link rel="next" href="reserved.variables.request.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.files" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.files.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/reserved.variables.files.php" />
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
 <li><a href="reserved.variables.post.php">$_POST</a></li>
 <li class="active"><a href="reserved.variables.files.php">$_FILES</a></li>
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
  <a href="reserved.variables.request.php">$_REQUEST<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.post.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_POST</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.files.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.files.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.files.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.files.php">French</option>
    <option value="de/reserved.variables.files.php">German</option>
    <option value="ja/reserved.variables.files.php">Japanese</option>
    <option value="pl/reserved.variables.files.php">Polish</option>
    <option value="ro/reserved.variables.files.php">Romanian</option>
    <option value="ru/reserved.variables.files.php">Russian</option>
    <option value="fa/reserved.variables.files.php">Persian</option>
    <option value="es/reserved.variables.files.php">Spanish</option>
    <option value="tr/reserved.variables.files.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.files" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_FILES</h1>
  <h1 class="refname">$HTTP_POST_FILES [deprecated]</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_FILES</span> -- <span class="refname">$HTTP_POST_FILES [deprecated]</span> &mdash; <span class="dc-title">HTTP File Upload variables</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.files-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative <span class="type"><a href="language.types.array.php" class="type array">array</a></span> of items uploaded to the current script
   via the HTTP POST method. 
  </p>

  <p class="simpara">
   <var class="varname"><var class="varname">$HTTP_POST_FILES</var></var> contains the same initial
   information, but is not a <a href="language.variables.superglobals.php" class="link">superglobal</a>. 
   (Note that <var class="varname"><var class="varname">$HTTP_POST_FILES</var></var> and <var class="varname"><var class="varname">$_FILES</var></var>
   are different variables and that PHP handles them as such)
  </p>

 </div>

 

 <div class="refsect1 changelog" id="refsect1-reserved.variables.files-changelog">
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
        Introduced <var class="varname"><var class="varname">$_FILES</var></var> that deprecated
        <var class="varname"><var class="varname">$HTTP_POST_FILES</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.files-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-reserved.variables.files-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"> <span class="function"><a href="function.move-uploaded-file.php" class="function" rel="rdfs-seeAlso">move_uploaded_file()</a> - Moves an uploaded file to a new location</span></li>
    <li class="member"><a href="features.file-upload.php" class="link">Handling File Uploads</a></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.request.php">$_REQUEST<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.post.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_POST</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.files.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.files&amp;redirect=http://www.php.net/manual/en/reserved.variables.files.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.files&amp;redirect=http://www.php.net/manual/en/reserved.variables.files.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_FILES</strong>
 </div><div id="allnotes">
 <a name="109283"></a>
 <div class="note">
  <strong class='user'>seifert at alesak dot net</strong>
  <a href="#109283" class="date">02-Jul-2012 02:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I just spent long time debugging strange behavior of one of our application on new webhosting. We have 30 file inputs on one page for upload to server. Problem was that only 20 was actually uploaded.<br />
<br />
Now I found there is an option max_file_uploads in php.ini limiting maximum size of $_FILES to 20 by default.<br />
<br />
When you have suhosin extension installed it has own option limiting same thing to 25 (suhosin.upload.max_uploads in php.ini)</span>
</code></div>
  </div>
 </div>
 <a name="108718"></a>
 <div class="note">
  <strong class='user'>Alexandre Teles</strong>
  <a href="#108718" class="date">20-May-2012 04:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can check error index this way:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$errorIndex </span><span class="keyword">= </span><span class="default">$_FILES</span><span class="keyword">[</span><span class="string">"file"</span><span class="keyword">][</span><span class="string">"error"</span><span class="keyword">];<br />
<br />
if (</span><span class="default">$errorIndex </span><span class="keyword">&gt; </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; die(</span><span class="string">'We have a error. Try Again.'</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">processFile</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108092"></a>
 <div class="note">
  <strong class='user'>codycoder at me dot com</strong>
  <a href="#108092" class="date">28-Mar-2012 01:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I find that using sizeof() works ok. <br />
<br />
EG:<br />
<br />
if(sizeof($_FILES)!=0){<br />
&nbsp;handleFiles();<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="107940"></a>
 <div class="note">
  <strong class='user'>yuriy dot nayda at gmail dot com</strong>
  <a href="#107940" class="date">15-Mar-2012 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
THis is an solution to convert Cyrillic and umlaut characters as file name when uplaoding files into needed encoding. Was searching for it but could not find. Thus posting this. Just like this:<br />
<br />
$value = mb_convert_encoding($value, "UTF-8");</span>
</code></div>
  </div>
 </div>
 <a name="106608"></a>
 <div class="note">
  <strong class='user'>BigShark666 at gmail dot com</strong>
  <a href="#106608" class="date">21-Nov-2011 10:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Nontypicall array comes in php after the submission.I wrote a small function to restate it to the familiar look.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">multiple</span><span class="keyword">(array </span><span class="default">$_files</span><span class="keyword">, </span><span class="default">$top </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$files </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$_files </span><span class="keyword">as </span><span class="default">$name</span><span class="keyword">=&gt;</span><span class="default">$file</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$top</span><span class="keyword">) </span><span class="default">$sub_name </span><span class="keyword">= </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'name'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else&nbsp; &nbsp; </span><span class="default">$sub_name </span><span class="keyword">= </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$sub_name</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">$sub_name</span><span class="keyword">) as </span><span class="default">$key</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$files</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">] = array(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'name'&nbsp; &nbsp;&nbsp; </span><span class="keyword">=&gt; </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'name'</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'type'&nbsp; &nbsp;&nbsp; </span><span class="keyword">=&gt; </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'type'</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'tmp_name' </span><span class="keyword">=&gt; </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'tmp_name'</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'error'&nbsp; &nbsp; </span><span class="keyword">=&gt; </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'error'</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'size'&nbsp; &nbsp;&nbsp; </span><span class="keyword">=&gt; </span><span class="default">$file</span><span class="keyword">[</span><span class="string">'size'</span><span class="keyword">][</span><span class="default">$key</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$files</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">multiple</span><span class="keyword">(</span><span class="default">$files</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">], </span><span class="default">FALSE</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$files</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">$file</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$files</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$_FILES</span><span class="keyword">);<br />
</span><span class="comment">/*<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [image] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; 400.png<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [type] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; image/png<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [tmp_name] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; /tmp/php5Wx0aJ<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [error] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; 0<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; 15726<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
)<br />
*/<br />
</span><span class="default">$files </span><span class="keyword">= </span><span class="default">multiple</span><span class="keyword">(</span><span class="default">$_FILES</span><span class="keyword">);<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$files</span><span class="keyword">);<br />
</span><span class="comment">/*<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [image] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; 400.png<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [type] =&gt; image/png<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [tmp_name] =&gt; /tmp/php5Wx0aJ<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [error] =&gt; 0<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; 15726<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
)<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="106558"></a>
 <div class="note">
  <strong class='user'>kbolyshev at gmail dot com</strong>
  <a href="#106558" class="date">18-Nov-2011 04:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For situation download[file1], download[file2], ..., download[fileN], try it:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/**<br />
&nbsp;*<br />
&nbsp;* @param array&nbsp; &nbsp;&nbsp; $arrayForFill<br />
&nbsp;* @param string&nbsp; &nbsp; $currentKey<br />
&nbsp;* @param mixed&nbsp; &nbsp;&nbsp; $currentMixedValue<br />
&nbsp;* @param string&nbsp; &nbsp; $fileDescriptionParam (name, type, tmp_name, error или size)<br />
&nbsp;* @return void<br />
&nbsp;*/<br />
</span><span class="keyword">function </span><span class="default">rRestructuringFilesArray</span><span class="keyword">(&amp;</span><span class="default">$arrayForFill</span><span class="keyword">, </span><span class="default">$currentKey</span><span class="keyword">, </span><span class="default">$currentMixedValue</span><span class="keyword">, </span><span class="default">$fileDescriptionParam</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$currentMixedValue</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$currentMixedValue </span><span class="keyword">as </span><span class="default">$nameKey </span><span class="keyword">=&gt; </span><span class="default">$mixedValue</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">rRestructuringFilesArray</span><span class="keyword">(</span><span class="default">$arrayForFill</span><span class="keyword">[</span><span class="default">$currentKey</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$nameKey</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$mixedValue</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$fileDescriptionParam</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$arrayForFill</span><span class="keyword">[</span><span class="default">$currentKey</span><span class="keyword">][</span><span class="default">$fileDescriptionParam</span><span class="keyword">] = </span><span class="default">$currentMixedValue</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$arrayForFill </span><span class="keyword">= array();<br />
foreach (</span><span class="default">$_FILES </span><span class="keyword">as </span><span class="default">$firstNameKey </span><span class="keyword">=&gt; </span><span class="default">$arFileDescriptions</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$arFileDescriptions </span><span class="keyword">as </span><span class="default">$fileDescriptionParam </span><span class="keyword">=&gt; </span><span class="default">$mixedValue</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">rRestructuringFilesArray</span><span class="keyword">(</span><span class="default">$arrayForFill</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$firstNameKey</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$_FILES</span><span class="keyword">[</span><span class="default">$firstNameKey</span><span class="keyword">][</span><span class="default">$fileDescriptionParam</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$fileDescriptionParam</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$_FILES </span><span class="keyword">= </span><span class="default">$arrayForFill</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105640"></a>
 <div class="note">
  <strong class='user'>unca dot alby at gmail dot com</strong>
  <a href="#105640" class="date">02-Sep-2011 01:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In checking the error code, you probably ought to check for code 4.&nbsp; I believe Code 4 means no file was uploaded, and there are many instances where that's perfectly OK.<br />
<br />
Such as when you have a form with multiple data items, including file and image uploads, plus whatever else.&nbsp; The user might not be adding a new upload for whatever reason, such as there may already be a file in the system from an earlier update, and the user is satisfied with that.</span>
</code></div>
  </div>
 </div>
 <a name="104395"></a>
 <div class="note">
  <strong class='user'>Sbastien</strong>
  <a href="#104395" class="date">13-Jun-2011 07:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're uploading multiple files and you name your file inputs "upload[]" the $_FILES array will look different than the var_dump posted below. I figured I'd post what it looks like since it caused me (and still causes me) headaches!<br />
<br />
array(1) {<br />
&nbsp;&nbsp;&nbsp; ["upload"]=&gt;array(5) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ["name"]=&gt;array(3) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0]=&gt;string(9)"file0.txt"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [1]=&gt;string(9)"file1.txt"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [2]=&gt;string(9)"file2.txt"<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ["type"]=&gt;array(3) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0]=&gt;string(10)"text/plain"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [1]=&gt;string(10)"text/plain"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [2]=&gt;string(10)"text/plain"<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ["tmp_name"]=&gt;array(3) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0]=&gt;string(14)"/tmp/blablabla"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [1]=&gt;string(14)"/tmp/phpyzZxta"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [2]=&gt;string(14)"/tmp/phpn3nopO"<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ["error"]=&gt;array(3) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [1]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [2]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ["size"]=&gt;array(3) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [0]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [1]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [2]=&gt;int(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
(I thought the array would have looked like upload[index][name] which is not the case.)</span>
</code></div>
  </div>
 </div>
 <a name="103662"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#103662" class="date">26-Apr-2011 02:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Having url rewrite patterns in .htaccess file which modify your urls can affect $_FILES sometimes. Even though the php page loads and works fine, this variable may not work because of it. Therefore if you rewrite 'www.example.com' to 'example.com', make sure you use the latter one when sending POST to the php page. I'm still not sure why this happens, but its worth noting here so others don't spend time chasing ghosts.</span>
</code></div>
  </div>
 </div>
 <a name="103522"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#103522" class="date">18-Apr-2011 06:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As mentioned , you should check the error index of the upload.<br />
<br />
Example below suggests you have a file field named 'image'.<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">$_FILES</span><span class="keyword">[</span><span class="string">'image'</span><span class="keyword">][</span><span class="string">'error'</span><span class="keyword">] == </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// success - move uploaded file and process stuff here<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}else{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// 'there was an error uploading file' stuff here....&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102252"></a>
 <div class="note">
  <strong class='user'>John</strong>
  <a href="#102252" class="date">03-Feb-2011 06:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the past you could unconditionally call $_FILES['profile_pic'] without ever having to worry about PHP spitting an "Undefined index: profile_pic" error (so long as the page posting had a file input on it (e.g. &lt;input type="file" name="profile_pic" /&gt;)). This was the case regardless of whether or not the end user actually uploaded a file. These days, with so many people browsing the web via iPads, you have to explicitly check to see if the input isset($_FILES['profile_pic']) before calling into it, else you'll get the aforementioned error message. This is because iOS devices running Safari disable file inputs thereby causing them to be treated as if they don't exist. Time to update your scripts!<br />
<br />
-john</span>
</code></div>
  </div>
 </div>
 <a name="92893"></a>
 <div class="note">
  <strong class='user'>mwgamera at gmail dot com</strong>
  <a href="#92893" class="date">13-Aug-2009 05:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To determine whether upload was successful you should check for error being UPLOAD_ERR_OK instead of checking the file size. When nothing is chosen to be uploaded, the key in $_FILES will still be there, but it should have error equal UPLOAD_ERR_NO_FILE.</span>
</code></div>
  </div>
 </div>
 <a name="91862"></a>
 <div class="note">
  <strong class='user'>calurion at gmail dot com</strong>
  <a href="#91862" class="date">29-Jun-2009 04:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For some reason when I tried to check if $_FILES['myVarName'] was empty() or !isset() or array_key_exists(), it always came back that the file was indeed in the superglobal, even when nothing was uploaded.<br />
<br />
I wonder if this is a result of enctype="multipart/form-data".<br />
<br />
Anyways, I solved my issue by checking to make sure that $_FILES['myVarName']['size'] &gt; 0</span>
</code></div>
  </div>
 </div>
 <a name="91041"></a>
 <div class="note">
  <strong class='user'>Sam</strong>
  <a href="#91041" class="date">21-May-2009 07:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is REQUIRED by the xhtml specs.</span>
</code></div>
  </div>
 </div>
 <a name="89674"></a>
 <div class="note">
  <strong class='user'>dewi at dewimorgan dot com</strong>
  <a href="#89674" class="date">18-Mar-2009 10:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The format of this array is (assuming your form has two input type=file fields named "file1", "file2", etc):<br />
<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [file1] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; MyFile.txt (comes from the browser, so treat as tainted)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [type] =&gt; text/plain&nbsp; (not sure where it gets this from - assume the browser, so treat as tainted)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [tmp_name] =&gt; /tmp/php/php1h4j1o (could be anywhere on your system, depending on your config settings, but the user has no control, so this isn't tainted)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [error] =&gt; UPLOAD_ERR_OK&nbsp; (= 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; 123&nbsp;&nbsp; (the size in bytes)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp;&nbsp; [file2] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; MyFile.jpg<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [type] =&gt; image/jpeg<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [tmp_name] =&gt; /tmp/php/php6hst32<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [error] =&gt; UPLOAD_ERR_OK<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; 98174<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
)<br />
<br />
Last I checked (a while ago now admittedly), if you use array parameters in your forms (that is, form names ending in square brackets, like several file fields called "download[file1]", "download[file2]" etc), then the array format becomes... interesting.<br />
<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [download] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [name] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file1] =&gt; MyFile.txt<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file2] =&gt; MyFile.jpg<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [type] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file1] =&gt; text/plain<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file2] =&gt; image/jpeg<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [tmp_name] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file1] =&gt; /tmp/php/php1h4j1o<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file2] =&gt; /tmp/php/php6hst32<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [error] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file1] =&gt; UPLOAD_ERR_OK<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file2] =&gt; UPLOAD_ERR_OK<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [size] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file1] =&gt; 123<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [file2] =&gt; 98174<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
)<br />
<br />
So you'd need to access the error param of file1 as, eg $_Files['download']['error']['file1']</span>
</code></div>
  </div>
 </div>
 <a name="88251"></a>
 <div class="note">
  <strong class='user'>andrewpunch at bigfoot dot com</strong>
  <a href="#88251" class="date">17-Jan-2009 12:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If $_FILES is empty, even when uploading, try adding enctype="multipart/form-data" to the form tag and make sure you have file uploads turned on.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.files&amp;redirect=http://www.php.net/manual/en/reserved.variables.files.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.files&amp;redirect=http://www.php.net/manual/en/reserved.variables.files.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.files.php">show source</a> |
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