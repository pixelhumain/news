<?php
// gettting asstes from parent module repo
$cssAnsScriptFilesModule = array( 
	'/css/news.css',
	'/js/news.js',
);
HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->getModule( News::MODULE )->getAssetsUrl() );


?>


<div>
    HELLO Clem

</div>

<script type="text/javascript">

modules.news = <?php echo json_encode( News::getConfig() ) ?> ;

jQuery(document).ready(function() {

    newsObj.init();
    

});



</script>