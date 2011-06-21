<!--- Contents --->		
		
    <div id="content">
	<!-- Title -->

	<div class="title-red">
            Discussion(s)
	</div>
	<!-- Speaker Information -->
	<div id="speaker">
            &nbsp;
	</div>
			
        <div class="bio">
            <?=ViewHelper::getMessageHtml($view->messages, $view->map)?>
	</div>		
	<div class="footer">&nbsp;</div>
    </div>
	  	  
<!--- Right Slash --->

    <!-- Events Date and Time -->
    <div id="right_sash">
		<!-- <div class="showLogo"></div> -->
	  /message/insert
	  <div class="title">
	  	<a id="svac" href="#">Add Discussion</a>
	  </div>
	  
	  <div id="detail" class="english svac">
	  
            <? if ($view->error != null) { ?>
		<div id="messages" style="padding-top: 10px; margin-top:5px; margin-bottom:10px; color:#FF0000; font-weight:bold;">
                    Error: <?=$view->error?>
		</div>
            <? } ?>
			  
            <? if($view->user != null) {
		$secret=md5(uniqid(rand(), true));
		$_SESSION['FORM_SECRET']=$secret;			
            ?>
                <form action="<?php echo Util::getLink('/message/insert'); ?>" method="post">
                    <h3>Title</h3>
                    <input type="text" name="title" size="40" />
                    <br /><br />
                    <h3>Content</h3>
                    <textarea name="content" cols="30" rows="7"></textarea>
                    <br /><br />
                    <input type="hidden" name="_submit" value="discussion" />
                    <input type="hidden" name="_form_secret" id="_form_secret" value="<?=$_SESSION['FORM_SECRET']?>" />
                    <input type="submit" value="Submit Discussion" onclick="this.disabled=true,this.form.submit();" />
                </form>
            <? } else { ?>
		You need to login to submit discussion ~
            <? } ?>
	</div>
	<br />
	<?=ViewHelper::getTutorialHtml($view->user)?>
    </div>
	  
	  