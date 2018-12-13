
<?php
class UpdateAction extends CAction
{
    public function run()
    {
    	$controller=$this->getController();
    	$result=News::update($_POST);
    	if(@$_GET["tpl"]=="co2"){
    		$params=array(
    			"news"=>array((string)$result["object"]["_id"]=>$result["object"]), 
    			"actionController"=>"save",
    			"canManageNews"=>true,
    			"canPostNews"=>true,
                "nbCol" => 1,
                "endStream"=>false,
                "pair" => false);
			echo $controller->renderPartial("news.views.co.timelineTree", $params,true);
    	}
		else
        	return Rest::json($result);
    }
}