<?php
// gettting asstes from parent module repo
$cssAnsScriptFilesModule = array( 
	'/css/news.css',
	'/css/timeline.css',
	'/css/form.css',
	'/js/news.js',
	'/js/init.js'
);
HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->getModule( News::MODULE )->getAssetsUrl() );

$cssAnsScriptFilesModule = array(
  "css/default/directory.css",
  "js/comments.js",
  "css/menus/multi_tags_scopes.css",
);
HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule,  Yii::app()->theme->baseUrl."/assets/" );
$cssAnsScriptFilesModule = array(
	'/js/scopes/scopes.js',
	'/js/default/directory.js',
	'/js/default/search.js',
	'/js/default/globalsearch.js',
	'/js/news/autosize.js',
  '/js/menus/multi_tags_scopes.js',
  '/js/cooperation/uiModeration.js',
);
HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->getModule( "co2" )->getAssetsUrl() );
$cssAnsScriptFiles = array(
    '/plugins/moment/min/moment.min.js' ,
    '/plugins/moment/min/moment-with-locales.min.js',
    '/plugins/jquery.dynForm.js',
    '/plugins/select2/select2.min.js' , 
    '/plugins/select2/select2.css',
    '/plugins/jquery.elastic/elastic.js',
    '/plugins/underscore-master/underscore.js',
    '/plugins/jquery-mentions-input-master/jquery.mentionsInput.js',
    '/plugins/jquery-mentions-input-master/jquery.mentionsInput.css',
    '/plugins/fine-uploader/jquery.fine-uploader/fine-uploader-gallery.css',
    '/plugins/fine-uploader/jquery.fine-uploader/jquery.fine-uploader.js',
    '/plugins/fine-uploader/jquery.fine-uploader/fine-uploader-new.min.css',
    '/plugins/facemotion/faceMocion.css',
    '/plugins/facemotion/faceMocion.js',
    '/plugins/showdown/showdown.min.js',
    '/plugins/to-markdown/to-markdown.js',
);
HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFiles, Yii::app()->request->baseUrl);


?>
<style type="text/css">
  <?php if($nbCol == 1){ ?>
    .timeline > li{
      width:100%;
    }
    .timeline::before {
      left:0;
    }
  <?php } ?>
</style>
<?php 
	$class="";
	if(@$inline && $inline) $class.="inline";
	if(@$nbCol && $nbCol) $class.=" nb-col-".$nbCol;
?> 
<div class="col-xs-12 padding-20 container-live <?php echo $class ?>">
	<?php if($formCreate!=="false"){ ?> 
		<div class="col-md-12 col-sm-12 col-xs-12 no-padding margin-bottom-15" 
	    	 style="<?php if(!@$isLive){ ?>padding-left:25px!important;<?php } ?>">
		<?php 
	        $params = array(
	                  "contextParentId" => @$contextParentId,
	                  "parent" => @$parent,
	                  "contextParentType" => @$contextParentType,
	                  "canManageNews" => @$canManageNews,
	                  "isLive" => @$isLive,
	                  "authorizedToStock" => @$authorizedToStock
	          );
	        $this->renderPartial('formCreateNews', $params);
	  	?>
		</div>
	<?php } ?>
	<ul class="timeline inline-block" id="news-list">
	  
	</ul>
</div>

<?php $this->renderPartial('modalModeration', array()); 
	if( @Yii::app()->session["userId"] ) $this->renderPartial('modalShare', array()); ?>

<script type="text/javascript" >
	modules.news = <?php echo json_encode( News::getConfig() ) ?> ;
	var news = {};
	var newsScopes={};
	var loadingData = false;
	var isLive = <?php echo json_encode(@$isLive) ?>;
	var indexStepNews = "<?php echo @$indexStep; ?>";
	var inline = "<?php echo @$inline; ?>";
	var nbCol = "<?php echo @$nbCol; ?>";
	var clickTarget = "<?php echo @$clickEvent; ?>";
	var scrollEvent = "<?php echo @$scroll; ?>";
	var clickEvent = (!scroll) ? true : false;
	var dateLimit = 0;  
	//var initLimitDate = <?php echo json_encode(@$limitDate) ?>;
	var contextParentType = <?php echo json_encode(@$contextParentType) ?>;
	var contextParentId = <?php echo json_encode(@$contextParentId) ?>;
	var canPostNews = <?php echo json_encode(@$canPostNews) ?>;
	var canManageNews = <?php echo json_encode(@$canManageNews) ?>;
	var uploadUrl = "<?php echo Yii::app()->params['uploadUrl'] ?>";
	var docType="<?php echo Document::DOC_TYPE_IMAGE; ?>";
	var contentKey = "<?php echo Document::IMG_SLIDER; ?>";
	var tagsNews = <?php echo json_encode($tags); ?>;
	//console.log("NEWS", news);
	jQuery(document).ready(function() {
		params={
			type:contextParentType,
			id: contextParentId,
			scroll: scrollEvent,
			scrollTarget: "window",
			click: clickEvent,
			nbCol : nbCol,
			clickTarget : clickTarget,
			containerTreeNews : "#news-list",
			dateLimit : dateLimit,
			indexStep: indexStepNews,
			inline : inline,
			isLive : isLive
		}
	    newsObj.init(params);  
	});
</script>