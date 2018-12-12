<?php
/**
 * CoController.php
 *
 * Cocontroller always works with the PH base 
 *
 * @author: Tibor Katelbach <tibor@pixelhumain.com>
 * Date: 14/03/2014
 */
class CoController extends CommunecterController {


    protected function beforeAction($action) {
        //parent::initPage();
		return parent::beforeAction($action);
  	}

  	public function actions(){
	    return array(
	        'index'   => 'news.controllers.actions.IndexAction',
	        'get'   => 'news.controllers.actions.GetAction',
	        'moderate'      => 'news.controllers.actions.ModerateAction',
	        'save'     		=> 'news.controllers.actions.SaveAction',
	        'delete'     	=> 'news.controllers.actions.DeleteAction',
	        'update'   		=> 'news.controllers.actions.UpdateAction',
			'share' 		=> 'news.controllers.actions.ShareAction',
	        'extractprocess' => array (
	            'class'   	=> 'ext.extract-url-content.ExtractProcessAction',
	            'options' 	=> array(
	                // Tmp dir to store cached resized images 
	                'cache_dir'   => Yii::getPathOfAlias('webroot') . '/assets/',	 
	                // Web root dir to search images from
	                'base_dir'    => Yii::getPathOfAlias('webroot') . '/',
	            )
	        )
	    );
	}
}
