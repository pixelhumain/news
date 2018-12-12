<?php
class SaveAction extends CAction
{
    public function run($type=null, $id= null)
    {
    	$controller=$this->getController();
    	$result=News::save($_POST);
    	if(!@$_GET["json"]){
    		$params=array(
    			"news"=>array( (string)$result["id"]=>$result["object"]), 
    			"actionController"=>"save",
    			"canManageNews"=>true,
    			"canPostNews"=>true,
                "endStream"=>false,
                "pair" => false);
			echo $controller->renderPartial("news.views.co.timelineTree", $params,true);
    	}
		else
        	return Rest::json($result);
    }
}