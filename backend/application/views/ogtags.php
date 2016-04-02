<html>
<head>
	<title>Maxletics</title>
	
	<?php if(isset($ogdata)) : ?>
	
		<!-- facebook --> 
		<!-- 
		<meta property="og:type" content="<?php //echo (empty($ogdata->video_url) ? 'website' :  'yt-fb-app:playlist'); ?>" />
		<meta property="fb:app_id" content="87741124305" />
		<meta property="og:type" content="<?php echo (empty($ogdata->video_url) ? 'website' :  'video'); ?>" />
		-->
		<meta property="fb:app_id" content="<?php echo $ogdata->fbAppId; ?>" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="<?php echo $ogdata->title; ?>" />
		<meta property="og:description" content="<?php echo $ogdata->description; ?>" />
		<!--  -->
		<meta property="og:url" content="<?php echo $ogdata->url; ?>" />
		
	<?php if( ! empty($ogdata->video_url)) : ?>
		<!-- application/x-shockwave-flash, video/mp4 -->
		<!-- 640 youtube, 360 vimeo -->
		<!-- -->
		<meta property="og:image" content="<?php echo $ogdata->video_img; ?>" />
		<meta property="og:video:url" content="<?php echo $ogdata->video_url; ?>" />
		<meta property="og:video:secure_url" content="<?php echo $ogdata->video_url; ?>" />
		<meta property="og:video:type" content="application/x-shockwave-flash" /> 
		<meta property="og:video:width" content="640" />
		<meta property="og:video:height" content="360" />
		
	<?php elseif( ! empty($ogdata->image)) : ?>
		<meta property="og:image" content="<?php echo $ogdata->image; ?>" />
		<meta property="og:image:type" content="<?php echo $ogdata->image_type; ?>" />
		<meta property="og:image:width" content="<?php echo $ogdata->image_width; ?>" />
		<meta property="og:image:height" content="<?php echo $ogdata->image_height; ?>" />
	<?php endif; ?>
		<!--
		<meta property="fb:app_id" content="1134815619882799" />
		-->			
			
		<!-- twitter -->    
		<!-- https://dev.twitter.com/docs/cards/validation/validator -->
		<!-- https://dev.twitter.com/cards/types/player ,	 https://cards-dev.twitter.com/validator -->
        <meta property="twitter:card" content="summary" />
        <meta name="twitter:site" content="<?php echo $ogdata->site; ?>" />
		<meta property="twitter:title" content="<?php echo $ogdata->title; ?>" />
		<meta property="twitter:description" content="<?php echo $ogdata->description; ?>" />
	<?php if( ! empty($ogdata->video_url)) : ?>
		<meta property="twitter:player" content="<?php echo $ogdata->video_url; ?>" />
		<meta property="twitter:player:width" content="480" />
		<meta property="twitter:player:height" content="360" />
		<meta property="twitter:image" content="<?php echo $ogdata->video_img; ?>" />	
		<?php elseif( ! empty($ogdata->image)) : ?>
		<meta property="twitter:image:src" content="<?php echo $ogdata->image; ?>" />
		<?php endif; ?>
			
	<?php endif; ?>
    
</head>
<body>

<p><?php //print_r($ogdata); ?></p>

</body>
</html>