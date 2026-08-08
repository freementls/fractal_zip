<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en" class="ydn-content-bg">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <meta name="description" content="The Yahoo! Developer Network offers Web Services and APIs to make it easy for developers to build applications and mashups">
                        <link rel="stylesheet" type="text/css" href="http://l.yimg.com/a/combo?/yui/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css&/yui/2.6.0/build/menu/assets/skins/sam/menu.css&/yui/2.6.0/build/button/assets/skins/sam/button.css&/yui/2.6.0/build/container/assets/skins/sam/container.css&/yui/2.6.0/build/resize/assets/skins/sam/resize.css&/yui/2.6.0/build/tabview/assets/skins/sam/tabview.css&/ydn/site/yui-2.6.0-treeview.css&/ydn/site/ydn-3947223076_49921.css&ydn/site/ydn_homepage-388579888_16998.css&">

<link rel="stylesheet" type="text/css" href="https://s.yimg.com/kj/ydn/combo?/common/css/ydn_header-y4R62MkkeBAlkxwgBkm7aA-.css&amp;/common/css/ydn_footer-Z44PAnQaiKCd32WX5YeGow-.css">
<link rel="stylesheet" type="text/css" href="/homepage/css/ydn_searchsuggest.css"><link rel="apple-touch-icon" href="http://l.yimg.com/a/i/ydn/ydn-iphone.png" type="image/png">
<link rel="shortcut icon" href="http://l.yimg.com/a/i/ydn/favicon2.ico" type="image/x-icon"><!--[if IE 6]>
<link rel="stylesheet" type="text/css" media="screen" href="http://l.yimg.com/a/lib/ydn/site/ie6-140422.css">
<![endif]-->
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" media="screen" href="http://l.yimg.com/a/lib/ydn/site/ie7-140042.css">
<![endif]-->
<script language="javascript" type="text/javascript" src="http://yui.yahooapis.com/3.3.0/build/yui/yui-min.js"></script>
<script type="text/javascript" src="/homepage/js/ydn_searchsuggest.js"></script>
<script type="text/javascript" src="/homepage/js/survey-min-1.js"></script>        <title>Yahoo! Developer Network</title>
    </head>
    <body>
        <div id="doc4" class="yui-t6">
            <div id="hd">
                <div id="ydn-header"><div id="ydn-header-univ-wrp" role="banner">
    <div class="ydn-header-univ-default">
    <link rel="stylesheet" type="text/css" href="http://l.yimg.com/zz/combo?kx/ucs/uh/css/287/yunivhead-min.css&kx/ucs/uh/css/221/logo-min.css&kx/ucs/notif_v2/css/145/notifications_v2-min.css&kx/ucs/mailcount/css/37/mail_preview-min.css&kx/ucs/search/css/190/search_all-min.css&kx/ucs/search/css/190/search_buttons-min.css"><style>#yUnivHead {background:none;}
#ydn-header .ydn-header-univ-default{overflow:visible;}</style><div id="yUnivHead" class="yucs-en-us" data-lang="en-us" data-property="ydn">        <a href="#yuhead-search" class="yucs-skipto-search yucs-activate">Skip to search.</a>        <div id="yuhead-hd" class="yuhead-clearfix">        <div id="yuhead-mepanel-cont">            <ul id="yuhead-mepanel" class="yucs-toolbar yucs-activate"  aria-label="User Services"><li class="yuhead-me yuhead-nodivide yuhead-nopad">    <a class="yuhead-signup" href="https://us.lrd.yahoo.com/_ylt=Al6piO9cDo.vJcUIgFfWTTuqEDsv/SIG=14cg6pj6k/EXP=1344965805/**https%3A//edit.yahoo.com/config/eval_register%3F.src=devnet%26.intl=us%26.lang=en-US%26.done=http%3A//developer.yahoo.com/" target="_top" rel="nofollow">    <em>New User?</em> Register</a></li><li class="yuhead-me">        <a href="https://us.lrd.yahoo.com/_ylt=AqGr53qOKuktwosWGKywXgGqEDsv/SIG=145tl6djt/EXP=1344965805/**https%3A//login.yahoo.com/config/login%3F.src=devnet%26.intl=us%26.lang=en-US%26.done=http%3A//developer.yahoo.com/"  target="_top" rel="nofollow">    <em>Sign In</em></a></li><li class="yuhead-me"><a href="http://us.lrd.yahoo.com/_ylt=Aq42lYd9MPWTNngPgWgl6fSqEDsv/SIG=121mreenk/EXP=1344965805/**http%3A//help.yahoo.com/l/us/yahoo/helpcentral/" rel="nofollow" target="_top">Help</a></li></ul>            <!-- empty lp cookie -->            <!-- empty lp beacon -->        </div>        <div id="yuhead-promo"><a href="http://us.lrd.yahoo.com/_ylt=AuHHHubdmlyhmckMQgG2OtaqEDsv/SIG=11hgl126o/EXP=1344965805/**http%3A//www.yahoo.com/bin/set/" target="_top" rel="nofollow">Make Y! My Homepage<abbr title="Yahoo!"></abbr></a></div>        <div id="yuhead-com-links-cont">            <ul id="yuhead-com-links" class="yucs-toolbar yucs-activate" aria-label="Yahoo! Services">                <li class="yucs-notifications yucs-notif-activate yuhead-com-link-item  yucs-wait">    <a class="ynotif-control yltasis" href="#" rel="nofollow" aria-haspopup="true" title="Notifications" aria-label="notifications" role="button"><span class="sp ynotif-ico-bell"></span><span class="ynotif-notif-count-con hide"><span class="ynotif-notif-count"></span></span>&nbsp;</a>    <div class="yucs-notif-panel hide"         data-ylt-bell="/;_ylt=Ao6RcKEV_XZmwr0PMuID3giqEDsv"        data-ylt-profile="/;_ylt=AnIm52RXvrWem0siSLnariaqEDsv"        data-ylt-app="/;_ylt=AkYU0_AXvOa4aLiSZDCKYpSqEDsv"        data-ylt-cta="/;_ylt=Arp33.ONGmdYIyfTqiMglJuqEDsv"        data-ylt-pageurl="/;_ylt=AtQ9nh7WVn8MaGsPZIuETjuqEDsv"          data-ylt-signin-cta="/;_ylt=AlCk_iOUM7IzptkVVUKA5Z.qEDsv"        data-guid=""        data-crumb="0ImUear15nh"        data-mode="2"        data-pagesize="10"                        data-view-more-txt="View More"        data-notif-user="A Yahoo! user"        data-intl="en-us"        data-yql-env=""        data-settings-tooltip="Settings"        data-app-aria-txt="App Icon"        data-view-all-txt="See All Notifications"        data-view-url="http://pulse.yahoo.com/y/notifications"        data-no-items-txt="There are currently no items."        data-no-notif-txt="You have no notifications."        data-notif-error-txt="Notifications with errors:"        data-get-notif-txt="Receive notifications when you comment or discuss content with friends on Yahoo!"        data-try-notif-txt="Learn more"        data-try-notif-link="http://help.yahoo.com//l/us/yahoo/comments/notifications/pnotification-03.html"        data-ylt-try-notif="/;_ylt=AqsHgapR2zL_dQ0cEAjNW_mqEDsv"         data-loading-txt="Loading..."        data-error-unavail-txt="Notifications are temporarily unavailable. Please try again later."        data-middleauth-nonotif-text="You have no new notifications."        data-password-verify-text="Please verify your password to view notifications."        data-authState="signedout"        data-signedout-cta-text="Sign In"        data-popup-login-url="https://login.yahoo.com/config/login_verify2?.pd=c%3DOIVaOGq62e5hAP8Tv..nr5E3&.src=sc"        data-signedout-message="Sign in to view notifications."        data-middleauth-text="You have new notifications.">        <div class="yucs-notif-title-bar yuhead-clearfix">            <span class="yucs-notif-panel-title">Notifications</span>             <a target="_blank" href="http://us.lrd.yahoo.com/_ylt=AiYWkITKT48mMoM4SMzD_BSqEDsv/SIG=12brcavar/EXP=1344965805/**http%3A//help.yahoo.com/l/us/yahoo/comments/notifications" class="yucs-notif-tools">Help</a>        </div>        <ul class="yucs-notif-items-panel" role="menu">        </ul>    </div></li>                <li class="yuhead-com-link-item yucs-mailpreview-ancestor">    <a class="sp yltasis yuhead-ico-mail" href="http://us.lrd.yahoo.com/_ylt=ApN4_ft.MDmDXysOp.9TMBmqEDsv/SIG=123snr7ou/EXP=1344965805/**http%3A//mail.yahoo.com/%3F.intl=us%26.lang=en-US" rel="nofollow" target="_top">Mail</a><ul class="yucs-mail-preview-panel hide"    data-mail-txt="Mail"    data-mail-view="View all Yahoo! Mail"    data-mail-help-txt="Help"    data-mail-help-url="http://help.yahoo.com/l/us/yahoo/mail/ymail/"    data-mail-loading-txt="Loading..."    data-languagetag="en-us"    data-authstate="signedout"    data-middleauth-signin-text="Click here to view your mail"    data-popup-login-url="https://login.yahoo.com/config/login_verify2?.pd=c%3DOIVaOGq62e5hAP8Tv..nr5E3&.src=sc"    data-middleauthtext="You have {count} new mail."    data-yltmessage-link="http://us.lrd.yahoo.com/_ylt=Al4V8FWcw.zVZ4x.nbHuSGKqEDsv/SIG=12f7v9f4k/EXP=1344965805/**http%3A//mrd.mail.yahoo.com/msg%3Fmid=%7BmsgID%7D%26fid=Inbox"    data-yltviewall-link="http://us.lrd.yahoo.com/_ylt=Apcp1RwbkyDBp1FwSXdUKDuqEDsv/SIG=11a4se1s0/EXP=1344965805/**http%3A//mail.yahoo.com/"    data-yltpanelshown="/;_ylt=AiBw2pPr2jG4qhJl4UVMJcuqEDsv"></ul></li><li class="yuhead-com-link-item">    <a href="http://my.yahoo.com/;_ylt=Aqh_sCgfUbVTHyY4yShKdnaqEDsv"     rel="nofollow"     target="_top">   My Y!    </a></li><li id="yuhead-com-home"><a class="sp yuhead-ico-home" href="http://us.lrd.yahoo.com/_ylt=AlLYEhZqf.JxZpmUJ_gsBRiqEDsv/SIG=119cvi7pn/EXP=1344965805/**http%3A//www.yahoo.com/" rel="nofollow" target="_top">Yahoo!</a></li>            </ul>        </div>    </div>    <div id="yuhead-bd" class="yuhead-clearfix">        <div class="yuhead-logo">   <style>      .yuhead-logo h2{        width:270px;        height:38px;        background-image:url(http://l.yimg.com/a/i/brand/purplelogo/uh/20/ydn/ydn.png);        _background-image:url(http://l.yimg.com/a/i/brand/purplelogo/uh/20/ydn/ydn-ffffff.gif);      }      .yuhead-logo a{        width:270px;        height:38px;      }      .yuhead-logo div.yuhead-comarketing {       width:270px;      }         </style>   <h2>      <a href="http://us.lrd.yahoo.com/_ylt=Atc01Hkx_5sHOxx.h9ktgyuqEDsv/SIG=11fji8q1g/EXP=1344965805/**http%3A//developer.yahoo.com/"       target="_top" tabindex="-1">         Yahoo! Developer Network      </a>   </h2>   <!-- comarketing component --></div>        <div id="yuhead-search">        <div id="yuhead-sform-cont" class="yuhead-s-web yuhead-search-form">        <form role="search" class="yucs-search yucs-activate" target="_top"    action="http://search.yahoo.com/search;_ylt=AqXuVpRv0yD5hVWVMHdoWI2qEDsv" method="get"><table role="presentation">   <tbody role="presentation"><tr role="presentation"><td class="yucs-form-input" role="presentation"> <label for="yuhead-sform-searchfield">     <span>Search</span>             </label>                 <input autocomplete="off" type="text" class="sp yuhead-ico-mglass yuhead-search-hint yucs-search-field" name="p"                  data-sh="Search"                  data-satype="rich"                  data-gosurl=""                  id="yuhead-sform-searchfield"                 data-pubid="" /></td><td NOWRAP class="yucs-form-btn" role="presentation"><div class="yucs-btn-wrap">    <button class="yucs-sweb-btn" type="submit">Search Web</button></div>            </td>         </tr>        </tbody></table><input type="hidden" id="fr" name="fr" value="ush-ydn" /><!-- desktop device --></form>            </div>    </div>        <!-- empty breaking news component -->    </div>                <!-- s2s -->    <!-- desktop device -->    </div>
	
<script language="javascript" src="http://l.yimg.com/zz/combo?kx/ucs/sts/js/290/skip-min.js&kx/ucs/uh/js/279/timestamp_library-min.js&kx/ucs/menu_utils/js/164/menu_utils_v2-min.js&kx/ucs/uh/js/267/aria_toolbar-min.js&kx/ucs/username/js/42/user_menu-min.js&kx/ucs/help/js/41/help_menu-min.js&kx/ucs/utility_link/js/20/utility_menu-min.js&kx/ucs/uh/js/262/logo_debug-min.js&kx/ucs/common/js/131/jsonp-cached-min.js&kx/ucs/notif_v2/js/151/notifications_bootstrap-min.js&kx/ucs/common/js/1/setup-min.js&kx/ucs/search/js/221/search-min.js&kx/ucs/search/js/210/search_text_dir-min.js&kx/ucs/uh/js/286/activate_library-min.js"></script>
<script language="javascript">
		YUI().use('node','event','event-mouseenter','substitute','oop','node-focusmanager','node', 'event', 'querystring-stringify', 'node-focusmanager', 'cookie', 'substitute', 'json','node','event','event-custom','event-valuechange','classnamemanager','node', function(Y) {});
</script>
    </div>
</div>
    <div id="ydn-nav-tier1-wrp" role="navigation">
        <ul id="ydn-nav-tier1"><li class=" ydn-nav-sel"><span>Developer</span><div></div></li><li><a href="http://developer.yahoo.com/publisher">Publisher</a><div></div></li><li class="last-tab "><a href="http://developer.yahoo.com/blogs/">Blog</a><div></div></li></ul>
    </div>
    <ul id="ydn-nav-tier2" role="navigation">
        <li class="ydn-nav-sel"><span>Home</span></li><li><a href="http://developer.yahoo.com/everything.html">APIs & Tools</a></li><li><a href="http://developer.yahoo.com/documentation">Documentation</a></li><li><a href="http://developer.yahoo.com/support">Support</a></li><li><a href="http://developer.yahoo.com/resources">Resources</a></li>        <li id="ydn-proj">
            <a href="https://developer.apps.yahoo.com/projects" title="My Projects">My Projects</a>
            <span></span> <!-- This span is for the My Projects icon as a background image. -->
        </li></ul>        <div id="ydn-search-wrp" role="search">
            <form id="ydn-search" action="http://search.yahoo.com/search" method="GET">
                <label for="p">YDN Search Field</label>
                <input id="p" type="text" name="p">
                <input type="hidden" id="ei" name="ei" value="UTF-8">
                <input type="hidden" id="vs" name="vs" value="developer.yahoo.com,developer.yahoo.net">
                <input class="ydn-btn-gry" type="submit" value="Search YDN">
            </form>
            <div id="ydn-recent-srch">
                <span>Recommended Topics:</span>
                <ul>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=yql" title="YQL">yql</a>
                    </li>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=updates" title="Updates">updates</a>
                    </li>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=apps" title="Apps">apps</a>
                    </li>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=yui" title="YUI">yui</a>
                    </li>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=hackday" title="Hackday">hackday</a>
                    </li>
                    <li>
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=oauth" title="OAuth">oauth</a>
                    </li>
                    <li class="last">
                        <a href="http://search.yahoo.com/search?ei=UTF-8&vs=developer.yahoo.com&p=patterns" title="Patterns">patterns</a>
                    </li>
                </ul>
            </div>
        </div>
</div>            </div>

            <div id="content-wrp">
                <style>
#ydn-doc-homepage-promo li.ydn-ext-an22 a
{
	background-image: url("http://l.yimg.com/a/i/ydn/sst/22/answers.gif");
	background-position: 0 0;
}
#ydn-doc-homepage-promo li.ydn-ext-geo22 a
{
	background-image: url("http://l.yimg.com/a/i/ydn/sst/22/placefinder.gif");
	background-position: 0 0;
}
#ydn-doc-homepage-promo li.ydn-ext-ca22 a
{
	background-image: url("http://l.yimg.com/a/i/ydn/sst/22/boss.gif");
	background-position: 0 0;
}
</style>
<div id="ydn-doc-homepage-promo" class="ydn-bx-g1">
    <div class="ydn-bx-g2">
        <h2>Using the APIs</h2>
        <p>
            <a class="ydn-doc-homepage-icon44-yql" href="/yql/"><img src="http://l.yimg.com/a/i/ydn/sst/44/yql.gif" alt="YQL"></a>
            <em>
                <a href="/yql/">YQL</a>
            </em>
            <a href="/yql/">(Yahoo! Query Language)</a>
        </p>
        <p>Query, filter, and join data across Web services through one simple language. Eliminate the need to learn how to call different APIs.</p>
        <p id="ydn-promo-p2">Access data from all these sources and more.</p>

        <ul class="ydn-ext22">
            <li class="ydn-ext-an22">
                <a title="Answers" href="http://developer.yahoo.com/yql/console/?_uiFocus=twitter&env=store://datatables.org/alltableswithkeys#h=select%20%2A%20from%20answers.search%20where%20query%3D%22cars%22%20and%20type%3D%22resolved%22%3B"></a>
            </li>
            <li class="ydn-ext-geo22">
                <a title="Place Finder" href="http://developer.yahoo.com/yql/console/?_uiFocus=twitter&env=store://datatables.org/alltableswithkeys#h=select%20%2A%20from%20geo.placefinder%20where%20text%3D%22sfo%22%3B"></a>
            </li>
            <li class="ydn-ext-ca22">
                <a title="Content Analysis" href="http://developer.yahoo.com/yql/console/?_uiFocus=twitter&env=store://datatables.org/alltableswithkeys#h=select%20%2A%20from%20contentanalysis.analyze%20where%20text%3D%27Italian%20sculptors%20and%20painters%20of%20the%20renaissance%20favored%20the%20Virgin%20Mary%20for%20inspiration%27%3B"></a>
            </li>
            <li class="ydn-ext-tw22">
                <a title="Twitter" href="http://developer.yahoo.com/yql/console/?_uiFocus=twitter&env=store://datatables.org/alltableswithkeys#h=select%20*%20from%20twitter.users%20where%20id%3D%27ydn%27%3B"></a>
            </li>
            <li class="ydn-ext-fb22">
                <a title="Facebook" href="http://developer.yahoo.com/yql/console/?_uiFocus=facebook&env=store://datatables.org/alltableswithkeys#h=desc%20facebook.admin.getAppProperties"></a>
            </li>
            <li class="ydn-ext-rss22">
                <a title="RSS" href="http://developer.yahoo.com/yql/console/?_uiFocus=rss&q=show%20tables&env=store://datatables.org/alltableswithkeys#h=select%20*%20from%20rss.multi.list%20where%20feeds%3D%22%27http%3A//code.flickr.com/blog/feed/rss/%27%2C%27http%3A//feeds.delicious.com/v2/rss/codepo8%3Fcount%3D15%27%22%20and%20html%3D%22true%22"></a>
            </li>
            <li class="ydn-ext-you22">
                <a title="Youtube" href="http://developer.yahoo.com/yql/console/?_uiFocus=youtube&q=show%20tables&env=store://datatables.org/alltableswithkeys#h=select%20*%20from%20youtube.user%20where%20id%3D%27yahoo%27"></a>
            </li>
            <li class="ydn-ext-ylp22">
                <a title="Yelp" href="http://developer.yahoo.com/yql/console/?_uiFocus=yelp&q=show%20tables&env=store://datatables.org/alltableswithkeys#h=desc%20yelp.review.search%3B"></a>
            </li>
        </ul>

        <ul id="ydn-promo-btns">
            <li>
                <a href="/yql/console/">Try the YQL Console</a>
            </li>
            <li>
                <a href="/yql/">Overview</a>
            </li>
            <li>
                <a href="/yql/guide/">Documentation</a>
            </li>
        </ul>
    </div>
</div><div id="ydn-doc-homepage-main" class="ydn-bx-g1">
    <div class="ydn-bx-g2">
        <a class="ydn-doc-view-all" href="/everything.html">&#187; view all</a>
        <h2>APIs &amp; Tools</h2>
        <dl>
          	<dt>
				<a href="/cocktails/mojito/"><img src="http://l.yimg.com/os/290/2012/03/30/mojito-44x44-png_090048.png" alt="Mojito" ></a>
			</dt>
          	<dd>
          		<em><a href="/cocktails/mojito/">Mojito</a></em>
          		<p>Mojito, is a free, open source JavaScript MVC framework for building high-performance, device-independent HTML5 applications running on both client and server.</p>
          		<ul>
            		<li><a href="/cocktails/mojito/docs/">Docs</a></li>
            		<li><a href="http://developer.yahoo.com/forum/Yahoo-Mojito">Community</a></li>
            		<li><a href="http://developer.yahoo.com/blogs/ydn/categories/cocktails/mojito">Blog</a></li>
          		</ul>
         	</dd>
        	<dt>
				<a href="/yui/"><img src="/homepage/img/yui-icon.png" width="44" alt="YUI Library" ></a>
			</dt>
          	<dd class="ydn-main-col2">
          		<em><a href="/yui/">YUI Library</a></em>
          		<p>YUI is a library of JavaScript utilities and controls for building richly interactive web applications using techniques such as DOM Scripting, DHTML, and Ajax.</p>
          		<ul>
            		<li><a href="http://yuilibrary.com/gallery/">Gallery</a></li>
            		<li><a href="/yui/3/api/">Docs</a></li>
            		<li><a href="http://yuilibrary.com/forum/">Community</a></li>
            		<li><a href="http://yuiblog.com/">Blog</a></li>
          		</ul>
          	</dd>
          	<dt>
				<a href="/search/boss/"><img src="http://l.yimg.com/a/i/ydn/sst/44/boss.gif" alt="Yahoo! Search BOSS" ></a>
			</dt>
          	<dd>
          		<em><a href="/search/boss/">Yahoo! Search BOSS</a></em>
          		<p>BOSS is Yahoo!'s open search web services platform. Developers can use BOSS to build and launch web-scale search products that utilize the entire Yahoo! Search index.</p>
          		<ul>
		            <li><a href="/search/boss/boss_api_guide/">Docs</a></li>
		            <li><a href="http://tech.groups.yahoo.com/group/ysearchboss/">Community</a></li>
		            <li><a href="http://ysearchblog.com/category/boss/ ">Blog</a></li>
		  		</ul>
          	</dd>
          	<dt>
				<a href="/geo/placefinder/"><img src="http://l.yimg.com/a/i/ydn/sst/44/placefinder.gif" alt="PlaceFinder" ></a>
			</dt>
	        <dd class="ydn-main-col2">
          		<em><a href="/geo/placefinder/">PlaceFinder</a></em>
          		<p>Yahoo! PlaceFinder converts street addresses and place names into geographic coordinates.</p>
	          	<ul>
	            	<li><a href="/geo/placefinder/guide/">Docs</a></li>
	            	<li><a href="http://developer.yahoo.com/forum/?showforum=124">Community</a></li>
	          	</ul>
	   		</dd>
        </dl>
    </div>
</div><div id="ydn-doc-homepage-rssfeed" class="ydn-bx-g1">
<div class="ydn-bx-g2">
<em>LATEST CHANGES</em>
<ul>
  <li>
  <a href="http://developer.yahoo.com/blogs/ydn/posts/2012/04/yahoo%e2%80%99s-mojito-is-now-open-source/">Yahoo!’s Mojito is Now Open ...</a>
  <span>at 07:59 AM, Apr 1, 2012</span>
  </li>  <li>
  <a href="http://developer.yahoo.com/blogs/ydn/posts/2012/02/welcome-yslow-open-source/">Welcome YSlow Open Source</a>
  <span>at 11:43 AM, Feb 16, 2012</span>
  </li>  <li>
  <a href="http://developer.yahoo.com/blogs/ydn/posts/2011/12/new-content-analysis-api-2/">New Content Analysis API</a>
  <span>at 10:32 AM, Dec 21, 2011</span>
  </li>
</ul>
</div>
</div><div id="ydn-homepage-nav-snippet">
  <ul>
    <li>
    <a id="ydn-proj-icon" href="https://developer.apps.yahoo.com/projects"><span></span></a>
    <a href="https://developer.apps.yahoo.com/projects">My Projects</a>
    <p>Your dashboard to create and manage your API Keys and Apps.</p>
    </li>
    <li>
    <a id="ydn-doc-icon" href="/documentation/"><span></span></a>
    <a href="/documentation/">Documentation</a>
    <p>Technical guides and tutorials of Yahoo! APIs and tools.</p>
    </li>
    <li>
    <a id="ydn-resource-icon" href="/resources/"><span></span></a>
    <a href="/resources/">Resources</a>
    <p>Yahoo!'s time tested experience and research to better your App's performance and usability.</p>
    </li>
    <li>
    <a id="ydn-support-icon" href="/support/"><span></span></a>
    <a href="/support/">Support</a>
    <p>Answers to your questions on Yahoo! APIs and tools. Join the community and spur innovation.</p>
    </li>
  </ul>
</div><div id="ydn-doc-homepage-featured">
        <h2>FEATURED ARTICLE</h2>

        <a href="http://developer.yahoo.com/blogs/ydn/posts/2012/04/yahoo%e2%80%99s-mojito-is-now-open-source/">Yahoo!’s Mojito is Now Open Source</a><span>by <em>Ren Waldura</em> on April 01, 2012</span><img src="http://ydn.zenfs.com/blogs/1/mojito_logo_top.png" /><p>

</p><p>I'm psyched to announce today the availability of Yahoo!’s Mojito in open source, a ground-breaking JavaScript framework developed by Yahoo! for Web developers. Mojito is one of the Yahoo! Cocktails, our JavaScript-centric presentation platform for connected devices.

</p><ul>
                <li><a href="http://developer.yahoo.com/blogs/ydn/posts/2012/04/yahoo%e2%80%99s-mojito-is-now-open-source/">&#187; read full article</a></li>
                <li><a href="http://developer.yahoo.com/blogs/ydn/">&#187; read all articles</a></li>
        </ul>
</div><div id="ydn-doc-homepage-events" class="yui-navset upcoming_events">
                     <ul class="yui-nav">
                         <li class="eventsheader nofeed"><a href="#tab0"><em>Events</em></a></li>
                         <li class="selected" style="border-left: 1px solid #BBC5CD;"><a href="#tab1"><em>ALL</em></a></li>
                         <li><a href="#tab2"><em>Upcoming</em></a></li>
                         <li><a href="#tab3"><em>YDN</em></a></li>
                    </ul>            
                    <div class="yui-content">
                         <div id="tab0"><span></span></div>
                         <div id="tab1">
                             <div class="event-wrap"><div class="event-item"><a href="http://developer.yahoo.com/blogs/ydn/posts/2012/02/hadoop-summit-2012-registration-now-open/">Hadoop Summit 2012 – Registration now open!</a><div class="event-date">Jun 12, 2012</div></div></div>
                         </div>
                         <div id="tab2">
                             No events planned
                         </div>
                         <div id="tab3">
                             <div class="event-wrap"><div class="event-item"><a href="http://developer.yahoo.com/blogs/ydn/posts/2012/02/hadoop-summit-2012-registration-now-open/">Hadoop Summit 2012 – Registration now open!</a><div class="event-date">Jun 12, 2012</div></div></div>
                         </div>
                    </div>
                    <div class="followevents">
                        <ul>
                            <li class="ydnreadall"><a href="http://developer.yahoo.com/blogs/ydn/categories/events-2/">&raquo Read blog posts about YDN events</a></li>
                            <li><a href="http://upcoming.yahoo.com/group/4081">&raquo Follow Yahoo! on Upcoming</a></li>
                        </ul>
                    </div>
                </div>
             <style>
             #ydn-doc-homepage-events { color:#333333; float:right; margin:24px 10px 0 0; overflow:hidden; padding:0 12px; width:671px; }
             #ydn-doc-homepage-events ul { background-color: transparent; border: 0; padding: 0 0 0 8px; }
             #ydn-doc-homepage-events ul li { line-height: 2; font-size: 11px; padding: 0 10px; margin: 0; border: 1px solid #BBC5CD; border-left: 0; border-bottom: 0; background-color: #FFF; }
             #ydn-doc-homepage-events ul li em { font-weight: bold; }
             #ydn-doc-homepage-events ul li a { text-decoration: none; color: #16387C; }
             #ydn-doc-homepage-events ul li.eventsheader { padding-left: 14px; padding-right: 11px; background: url("http://l.yimg.com/a/i/ydn/wp/thm/ydn/feed-small.png") no-repeat scroll 0 50% transparent; border-top: 0; border-right: 0; }
             #ydn-doc-homepage-events ul li.eventsheader a em { color: #000; font-family:arial; font-weight:bold; text-transform:uppercase; font-size: 13px;}
             #ydn-doc-homepage-events ul li.nofeed { background: transparent; cursor: default; }
             #ydn-doc-homepage-events ul li.nofeed a { cursor: default; }
             #ydn-doc-homepage-events ul li.selected { background: #364D62; }
             #ydn-doc-homepage-events ul li.selected { background: -moz-linear-gradient(-90deg, #5C7583 0pt, #364D62 100%) repeat-x scroll left top #364D62; }
             #ydn-doc-homepage-events ul li.selected a { background: url("http://l.yimg.com/a/i/ydn/wp/thm/ydn/tab-arrow-down.png") no-repeat scroll center bottom transparent; color: #FFF; margin-bottom: -5px; }
             #ydn-doc-homepage-events ul li.selected a em { margin-bottom: 5px; }
             #ydn-doc-homepage-events .yui-content { background-color: transparent; border-top: 1px solid #BBC5CD; padding: 13px; padding-top: 7px; }
             #ydn-doc-homepage-events .yui-content .event-wrap:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
             #ydn-doc-homepage-events .yui-content .event-item { float: left; width: 192px; height: 48px; margin: 8px; }
             #ydn-doc-homepage-events .yui-content .event-item a { text-decoration: none; color: #114D8D; font-size: 13px; }
             #ydn-doc-homepage-events .yui-content .event-item .event-date { color: #999; font-size: 11px; }
             #ydn-doc-homepage-events .yui-content .event-item .event-date img { margin: 0 0 -3px 3px; }
             #ydn-doc-homepage-events div.followevents ul { float: right; background-color: transparent; }
             #ydn-doc-homepage-events div.followevents ul li { float: left; background-color: transparent; border: 0; }
             #ydn-doc-homepage-events div.followevents ul li.ydnreadall { border-right: 1px solid #BBC5CD; }
             #ydn-doc-homepage-events div.followevents ul li a { font-size: 13px; font-weight: bold; color: #114D8D; }
             #ydn-doc-homepage-events ul li { margin: 0px -2px\9; }
             </style>
                    <div id="ft">
                    
<!-- footer -->
<div id="ydn-footer">
  <ul class="ydn-ext22">
    <li class="ydn-ext-fb22">
	  <a href="http://www.facebook.com/yahoodevelopernetwork" title="Facebook">Facebook</a>
	</li>
    <li class="ydn-ext-tw22">
	  <a href="http://twitter.com/ydn" title="Twitter">Twitter</a>
	</li>
    <li class="ydn-ext-up22">
      <a href="http://upcoming.yahoo.com/group/4081/" title="Upcoming">Upcoming</a>
    </li>
    <li class="ydn-ext-git22">
      <a href="http://github.com/yahoo/" title="GitHub">GitHub</a>
    </li>
    <li class="ydn-ext-rss22">
      <a href="http://feeds.developer.yahoo.net/YDNBlog" title="YDN Blog">YDN Blog</a>
    </li>
  </ul>
  <span>Follow Yahoo! Developer Network on</span>
  <ul class="ydn-breadcrumb" role="navigation">
  <li><a href="http://developer.yahoo.com/">Developer</a></li>  <li class="ydn-separater">Home</li>
</ul>
  <div class="ydn-footer-content" role="contentinfo">Copyright &copy; 2012 Yahoo! Inc. All rights reserved.</div>
  <ul class="ydn-tou">
    <li><a href="http://info.yahoo.com/copyright/us/details.html">Copyright</a></li>
    <li class="ydn-divider-left"><a href="http://info.yahoo.com/privacy/us/yahoo/devel/details.html">Privacy Policy</a></li>
    <li class="ydn-divider-left"><a href="http://info.yahoo.com/legal/us/yahoo/api/api-2140.html">Terms of Use</a></li>
  </ul>
  <ul class="ydn-contact">
    <li><a href="http://developer.yahoo.com/register/">Contact Us</a></li>
    <li class="ydn-divider-left"><a href="http://developer.yahoo.net/forum/">Community</a></li>
    <li class="ydn-divider-left"><a href="http://developer.yahoo.net/forum/?showforum=22">Suggestions</a></li>
  </ul>
</div>
<!-- end footer -->
                </div>
            </div>
        </div>

        <script language="javascript" type="text/javascript" src="http://l.yimg.com/a/combo?/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js&/yui/2.6.0/build/animation/animation-min.js&/yui/2.6.0/build/container/container-min.js&/yui/2.5.2/build/menu/menu-min.js&/yui/2.9.0/build/element/element-min.js&/yui/2.6.0/build/treeview/treeview-min.js&/yui/2.6.0/build/connection/connection-min.js&/yui/2.6.0/build/json/json-min.js&/yui/2.6.0/build/dragdrop/dragdrop-min.js&/yui/2.6.0/build/selector/selector-beta-min.js&/yui/2.5.2/build/tabview/tabview-min.js&/yui/2.9.0/build/button/button-min.js&/yui/2.6.0/build/get/get-min.js&/yui/2.6.0/build/datasource/datasource-min.js&/yui/2.6.0/build/datatable/datatable-min.js&/ydn/site/ydn-105031.js&"></script>

<script>
var lifeed = "";
var eventstab = new YAHOO.widget.TabView("ydn-doc-homepage-events");
eventstab.on("beforeActiveTabChange", function(e,a,b) {
if(e.newValue._configs.label.value == "Events")
{
    var url = "";
    var whichtab = e.prevValue._configs.label.value;
    if(whichtab.toLowerCase() == "upcoming")
    {
        url = "http://upcoming.yahoo.com/syndicate/v2/group/4081/87b95fde86";
    }
    else if(whichtab.toLowerCase() == "ydn")
    {
        url = "http://developer.yahoo.com/blogs/ydn/categories/events-2/feed/rss2/";
    }
    if(url != "")
    {
        document.location.href = url;
    }
    return false;
}
else if(e.newValue._configs.label.value.toLowerCase() == "all")
{
    lifeed = YAHOO.util.Dom.getElementsByClassName("eventsheader");
    if(!YAHOO.util.Dom.hasClass(lifeed[0], "nofeed"))
    {
        YAHOO.util.Dom.addClass(lifeed[0], "nofeed");
    }
}
else
{
    lifeed = YAHOO.util.Dom.getElementsByClassName("eventsheader");
    if(YAHOO.util.Dom.hasClass(lifeed[0], "nofeed"))
    {
        YAHOO.util.Dom.removeClass(lifeed[0], "nofeed");
    }
}
});
</script><script src="http://l.yimg.com/ss/rapid_2.5.0.js"></script>
<script> var keys = {A_pn:'Developer Network Homepage'}; var conf = {spaceid:792400042, tracked_mods:['ydn-doc-homepage-promo', 'ydn-doc-homepage-main', 'ydn-doc-homepage-rssfeed', 'ydn-doc-homepage-nav-snippet', 'ydn-doc-homepage-features', 'ydn-doc-homepage-events', 'ydn-header-univ-wrp', 'ydn-nav-tier1-wrp', 'ydn-nav-tier2', 'ydn-search-wrp', 'ydn-footer'], keys:keys, ywa: { project_id: 10001393677061 }};	var ins = new YAHOO.i13n.Track(conf); 
	ins.init(); 
</script>     </body>
</html>
<script type="text/javascript"src="http://l.yimg.com/d/lib/rt/rto1_78.js"></script><script>var rt_page="792400042:FRTMA"; varrt_ip="24.157.179.52";if ("function" == typeof(rt_AddVar) ){ rt_AddVar("ys", escape("19198B62")); rt_AddVar("cr", escape("bRNwlel73BU"));rt_AddVar("sg", escape("/SIG=11o1ud0mra9u87md80lioh&b=3&s=i0/1343756205/24.157.179.52/19198B62")); rt_AddVar("yd", escape("1136643880"));}</script><noscript><img src="http://rtb.pclick.yahoo.com/images/nojs.gif?p=792400042:FRTMA"></noscript><!-- SpaceID=792400042 loc=FSRVY noad -->
<script language=javascript>
if(window.yzq_d==null)window.yzq_d=new Object();
window.yzq_d['pFPKaWKL5Nc-']='&U=12dt1fcij%2fN%3dpFPKaWKL5Nc-%2fC%3d-1%2fD%3dFSRVY%2fB%3d-1%2fV%3d0';
</script><noscript><img width=1 height=1 alt="" src="http://us.bc.yahoo.com/b?P=1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB&T=181aptqdq%2fX%3d1343756205%2fE%3d792400042%2fR%3ddev_net%2fK%3d5%2fV%3d2.1%2fW%3dH%2fY%3dYAHOO%2fF%3d4089378826%2fH%3dc2VydmVJZD0iMUZMOVYyS0xHVW1rLlFlelVBckxFUTE3R0oyek5GQVlGNjBBQmZuQiIgc2l0ZUlkPSI0NDY1NTUxIiB0U3RtcD0iMTM0Mzc1NjIwNTQyMzcwNSIg%2fQ%3d-1%2fS%3d1%2fJ%3d19198B62&U=12dt1fcij%2fN%3dpFPKaWKL5Nc-%2fC%3d-1%2fD%3dFSRVY%2fB%3d-1%2fV%3d0"></noscript><script language=javascript>
if(window.yzq_d==null)window.yzq_d=new Object();
window.yzq_d['oVPKaWKL5Nc-']='&U=13e21kcei%2fN%3doVPKaWKL5Nc-%2fC%3d289534.9603437.10326224.9298098%2fD%3dFOOT%2fB%3d4123617%2fV%3d1';
</script><noscript><img width=1 height=1 alt="" src="http://us.bc.yahoo.com/b?P=1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB&T=181htjo61%2fX%3d1343756205%2fE%3d792400042%2fR%3ddev_net%2fK%3d5%2fV%3d2.1%2fW%3dH%2fY%3dYAHOO%2fF%3d1915613994%2fH%3dc2VydmVJZD0iMUZMOVYyS0xHVW1rLlFlelVBckxFUTE3R0oyek5GQVlGNjBBQmZuQiIgc2l0ZUlkPSI0NDY1NTUxIiB0U3RtcD0iMTM0Mzc1NjIwNTQyMzcwNSIg%2fQ%3d-1%2fS%3d1%2fJ%3d19198B62&U=13e21kcei%2fN%3doVPKaWKL5Nc-%2fC%3d289534.9603437.10326224.9298098%2fD%3dFOOT%2fB%3d4123617%2fV%3d1"></noscript><!--QYZ ,;;;792400042;;--><script language=javascript>
if(window.yzq_p==null)document.write("<scr"+"ipt language=javascript src=http://l.yimg.com/d/lib/bc/bc_2.0.5.js></scr"+"ipt>");
</script><script language=javascript>
if(window.yzq_p)yzq_p('P=1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB&T=17svsl8g5%2fX%3d1343756205%2fE%3d792400042%2fR%3ddev_net%2fK%3d5%2fV%3d1.1%2fW%3dJ%2fY%3dYAHOO%2fF%3d1857015005%2fH%3dc2VydmVJZD0iMUZMOVYyS0xHVW1rLlFlelVBckxFUTE3R0oyek5GQVlGNjBBQmZuQiIgc2l0ZUlkPSI0NDY1NTUxIiB0U3RtcD0iMTM0Mzc1NjIwNTQyMzcwNSIg%2fS%3d1%2fJ%3d19198B62');
if(window.yzq_s)yzq_s();
</script><noscript><img width=1 height=1 alt="" src="http://us.bc.yahoo.com/b?P=1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB&T=181s36rs6%2fX%3d1343756205%2fE%3d792400042%2fR%3ddev_net%2fK%3d5%2fV%3d3.1%2fW%3dJ%2fY%3dYAHOO%2fF%3d2714640366%2fH%3dc2VydmVJZD0iMUZMOVYyS0xHVW1rLlFlelVBckxFUTE3R0oyek5GQVlGNjBBQmZuQiIgc2l0ZUlkPSI0NDY1NTUxIiB0U3RtcD0iMTM0Mzc1NjIwNTQyMzcwNSIg%2fQ%3d-1%2fS%3d1%2fJ%3d19198B62"></noscript><script language=javascript>
(function(){window.xzq_p=function(R){M=R};window.xzq_svr=function(R){J=R};function F(S){var T=document;if(T.xzq_i==null){T.xzq_i=new Array();T.xzq_i.c=0}var R=T.xzq_i;R[++R.c]=new Image();R[R.c].src=S}window.xzq_sr=function(){var S=window;var Y=S.xzq_d;if(Y==null){return }if(J==null){return }var T=J+M;if(T.length>P){C();return }var X="";var U=0;var W=Math.random();var V=(Y.hasOwnProperty!=null);var R;for(R in Y){if(typeof Y[R]=="string"){if(V&&!Y.hasOwnProperty(R)){continue}if(T.length+X.length+Y[R].length<=P){X+=Y[R]}else{if(T.length+Y[R].length>P){}else{U++;N(T,X,U,W);X=Y[R]}}}}if(U){U++}N(T,X,U,W);C()};function N(R,U,S,T){if(U.length>0){R+="&al="}F(R+U+"&s="+S+"&r="+T)}function C(){window.xzq_d=null;M=null;J=null}function K(R){xzq_sr()}function B(R){xzq_sr()}function L(U,V,W){if(W){var R=W.toString();var T=U;var Y=R.match(new RegExp("\\\\(([^\\\\)]*)\\\\)"));Y=(Y[1].length>0?Y[1]:"e");T=T.replace(new RegExp("\\\\([^\\\\)]*\\\\)","g"),"("+Y+")");if(R.indexOf(T)<0){var X=R.indexOf("{");if(X>0){R=R.substring(X,R.length)}else{return W}R=R.replace(new RegExp("([^a-zA-Z0-9$_])this([^a-zA-Z0-9$_])","g"),"$1xzq_this$2");var Z=T+";var rv = f( "+Y+",this);";var S="{var a0 = '"+Y+"';var ofb = '"+escape(R)+"' ;var f = new Function( a0, 'xzq_this', unescape(ofb));"+Z+"return rv;}";return new Function(Y,S)}else{return W}}return V}window.xzq_eh=function(){if(E||I){this.onload=L("xzq_onload(e)",K,this.onload,0);if(E&&typeof (this.onbeforeunload)!=O){this.onbeforeunload=L("xzq_dobeforeunload(e)",B,this.onbeforeunload,0)}}};window.xzq_s=function(){setTimeout("xzq_sr()",1)};var J=null;var M=null;var Q=navigator.appName;var H=navigator.appVersion;var G=navigator.userAgent;var A=parseInt(H);var D=Q.indexOf("Microsoft");var E=D!=-1&&A>=4;var I=(Q.indexOf("Netscape")!=-1||Q.indexOf("Opera")!=-1)&&A>=4;var O="undefined";var P=2000})();
</script><script language=javascript>
if(window.xzq_svr)xzq_svr('http://csc.beap.bc.yahoo.com/');
if(window.xzq_p)xzq_p('yi?bv=1.0.0&bs=(128lnfdsa(gid$1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB,st$1343756205423705,v$1.0))&t=J_3-D_3');
if(window.xzq_s)xzq_s();
</script><noscript><img width=1 height=1 alt="" src="http://csc.beap.bc.yahoo.com/yi?bv=1.0.0&bs=(128lnfdsa(gid$1FL9V2KLGUmk.QezUArLEQ17GJ2zNFAYF60ABfnB,st$1343756205423705,v$1.0))&t=J_3-D_3"></noscript>
<!-- p2.ydn.bf1.yahoo.com compressed/chunked Tue Jul 31 10:36:45 PDT 2012 -->
