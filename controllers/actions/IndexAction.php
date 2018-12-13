<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null, $date = null, $isLive = null,$streamType="news", $textSearch=null){
    	$ctrl = $this->getController();
     	$ctrl->layout = "//layouts/empty";
     	$news = array();
		$params=array(
			"contextParentType" => $type, 
			"contextParentId" => $id,
			"authorizedToStock"=> Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE),			
			"tags" => Tags::getActiveTags(),
			"edit" => false,
			"openEdition" => false,
			"canPostNews"=>false,
			"canManageNews"=>false,
			"formCreate"=>true,
			"indexStep"=>6,
			"nbCol"=>2,
			"inline"=> false,
			"scroll"=>true,
			"clickEvent"=>false
		);
		if(@$parent){
			$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $parent["_id"]);
			$params["openEdition"] = Authorisation::isOpenEdition($parent["_id"], $type, @$parent["preferences"]);
		}
		if(@Yii::app()->session["userId"]){
			$params["canPostNews"]=true;
			if(in_array($type, [Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION, Place::COLLECTION])){
				$parent = Element::getElementSimpleById($id,$type,null, array("links"));
				if($type!= Event::COLLECTION && @$parent["links"][Element::$connectTypes[$type]][Yii::app()->session["userId"]] 
					&& !@$parent["links"][Element::$connectTypes[$type]][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
					$params["canManageNews"]=true;
				else if($type==Event::COLLECTION && ((@$parent["links"]["attendees"][Yii::app()->session["userId"]] && @$parent["links"]["attendees"][Yii::app()->session["userId"]]["isAdmin"]) || @$parent["links"]["organizer"][Yii::app()->session["userId"]]) )
            		$params["canManageNews"]=true;
            }else if($type == Person::COLLECTION && Yii::app()->session["userId"]==$id && $isLive!=true)
					$params["canManageNews"]=true;

        }
        $params["parent"]=@$parent;
		$params["isLive"]=(@$isLive && $isLive != null) ? $isLive : false;
		if(@$_POST){
			$params["formCreate"] = @$_POST["formCreate"] ? $_POST["formCreate"] : true;
			$params["nbCol"] = @$_POST["nbCol"] ? $_POST["nbCol"] : 2;
			$params["inline"] = @$_POST["inline"] ? $_POST["inline"] : false;
			$params["indexStep"] = @$_POST["indexStep"] ? $_POST["indexStep"] : 6;
			$params["scroll"] = @$_POST["scroll"] ? $_POST["scroll"] : true;
			$params["clickEvent"] = @$_POST["clickEvent"] ? $_POST["clickEvent"] : false;
		} else if (@$_GET){
			$params["formCreate"] = @$_GET["formCreate"] ? $_GET["formCreate"] : true;
			$params["nbCol"] = @$_GET["nbCol"] ? $_GET["nbCol"] : 2;
			$params["inline"] = @$_GET["inline"] ? $_GET["inline"] : false;
			$params["indexStep"] = @$_GET["indexStep"] ? $_GET["indexStep"] : 6;
			$params["scroll"] = @$_GET["scroll"] ? $_GET["scroll"] : true;
			$params["clickEvent"] = @$_GET["clickEvent"] ? $_GET["clickEvent"] : false;
		}
		if (in_array($type,[Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION, Person::COLLECTION]))
			$params["deletePending"] = Element::isElementStatusDeletePending($type, $id);

		echo $ctrl->render("news.views.co.index", $params);
    }
}