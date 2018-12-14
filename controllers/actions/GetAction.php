<?php
class GetAction extends CAction
{
    public function run($type=null, $id= null, $date = null, $isLive = null){
    	$controller = $this->getController();
     	$controller->layout = "//layouts/empty";
     	$news = array();
		$date=new MongoDate((@$date && !empty($date)) ? $date : time());
		$params = array();
		$where = array();
		$params["type"] = @$type; 
		//Define condition of each wall generated datas
		if($type == Person::COLLECTION) {
			$parent = Element::getElementSimpleById( $id,$type,null, array("links"));
            if (@Yii::app()->session["userId"]){
				$params["canManageNews"]=($id == Yii::app()->session["userId"] && $isLive!=true) ? true : false;
			}
			if(@$isLive && (@Yii::app()->session["userId"] && $id == Yii::app()->session["userId"])){				$authorFollowedAndMe=[];
				$arrayIds=[$id];
				$followsArrayIds=[];
				if(@$parent["links"]["memberOf"] && !empty($parent["links"]["memberOf"])){
					foreach ($parent["links"]["memberOf"] as $key => $data){
						if(!@$data[Link::TO_BE_VALIDATED])
							array_push($arrayIds,$key);
					}
				}
				if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
					foreach ($parent["links"]["projects"] as $key => $data){
						if(!@$data[Link::TO_BE_VALIDATED])
							array_push($arrayIds,$key);
					}
				}
				if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
					foreach ($parent["links"]["events"] as $key => $data){
						if(!@$data[Link::TO_BE_VALIDATED])
							array_push($arrayIds,$key);
					}
				}
				if(@$parent["links"]["follows"] && !empty($parent["links"]["follows"])){
					foreach ($parent["links"]["follows"] as $key => $data)
						array_push($followsArrayIds,$key);
				}
		        $where = array(
		        	'$and' => array(
						array('$or'=> 
							array(
								array("author"=>$id), 
								array("sharedBy.id"=>array('$in'=>array($id))), 
								array("sharedBy.id"=>array('$in'=>array($arrayIds))), 
								array("target.id" =>  array('$in' => $arrayIds)),
								array("mentions.id" => array('$in' => $arrayIds)),
								array(
									"target.id"=> array('$in' => $followsArrayIds),
									"sharedBy.id"=> array('$in' => $followsArrayIds),
									"scope.type" => array('$in'=> ['public','restricted'])
								)
							)
						),
						array("type" => array('$ne' => "pixels")),
		        	)	
		        );
			}
			else{
				$scope=["public","restricted"];
				if (@$params["canManageNews"] && $params["canManageNews"]){
					$orRequest=array(
						array("author"=> $id,"targetIsAuthor"=>array('$exists'=>false),"type"=>"news"), 
						array("target.id"=> $id, "target.type" => Person::COLLECTION),
						array("sharedBy.id"=>array('$in'=>array($id)), "verb"=> "share"),
					);
				} else {
					$orRequest=array(
						array("author"=> $id,
							"targetIsAuthor"=>array('$exists'=>false),
							//"type"=>"news", 
							"scope.type"=> array('$in'=> $scope)
						), 
						array("target.id"=> $id, "scope.type"=> array('$in'=> $scope),
						array("sharedBy.id"=>array('$in'=>array($id)),"verb"=> "share"))
					);
				}
				if((!@$params["canManageNews"] || $params["canManageNews"] == false ) && @Yii::app()->session["userId"]){
					array_push($orRequest,
								array("author"=> Yii::app()->session["userId"],
										"target.id"=> $id)
							);
				}
				$where = array('$or' => $orRequest);
			}
		}
		else if(in_array($type, [Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION, Place::COLLECTION])){
			$parent = Element::getElementSimpleById($id,$type,null, array("links"));
			if(@Yii::app()->session["userId"]){
				if($type!= Event::COLLECTION && @$parent["links"][Element::$connectTypes[$type]][Yii::app()->session["userId"]] 
					&& !@$parent["links"][Element::$connectTypes[$type]][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
					$params["canManageNews"]=true;
				else if($type==Event::COLLECTION && 
					((@$parent["links"]["attendees"][Yii::app()->session["userId"]] && @$parent["links"]["attendees"][Yii::app()->session["userId"]]["isAdmin"]) ||
            		 @$parent["links"]["organizer"][Yii::app()->session["userId"]]) )
            		$params["canManageNews"]=true;
            }
			$scope=["public","restricted"];
			$arrayIds=[];
			if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
				foreach ($parent["links"]["projects"] as $key => $data){
					if(!@$data[Link::TO_BE_VALIDATED])
						array_push($arrayIds,$key);
				}
			}
			if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
				foreach ($parent["links"]["events"] as $key => $data){
					if(!@$data[Link::TO_BE_VALIDATED])
						array_push($arrayIds,$key);
				}
			}
			if (@$params["canManageNews"] && $params["canManageNews"]){
				$orRequest=array(
					array("mentions.id"=>$id,"scope.type"=>array('$in'=>$scope)),
					array("target.id"=>$id)
				);
			}else{
				$orRequest=array(
					array("mentions.id"=>$id,"scope.type"=>array('$in'=>$scope)),
					array("target.id"=>$id, 
							'$or'=> array(
							array("scope.type"=>array('$in'=>$scope)),
							array("author"=>Yii::app()->session["userId"])
						)
					)
				);
			}
			array_push($orRequest,
				array('$or'=>array(
						array("sharedBy.id"=>array('$in'=>array($arrayIds))), 
						array("target.id" =>  array('$in' => $arrayIds))),
					"scope.type"=>array('$in'=>$scope)));
			$where = array('$or'=>$orRequest);
		}
		else{
			/***********************************  DEFINE LOCALITY QUERY   ***************************************/
	  		
			
			$where = array( "scope.type" => "public",
	  						"target.type" => array('$ne' => "pixels"),
	  						);

	  		//if(@$_POST["typeNews"]) $where["type"] = $_POST["typeNews"];
	
			
	  		
	  	}

	 			
		
		//Exclude => If there is more than 5 reportAbuse
		/*$where['$and'][] =  array('$or'=>array(array("reportAbuseCount" => array('$lt' => 5)),
												array("reportAbuseCount" => array('$exists'=>0))
											  ));*/
		//Exclude => If isAnAbuse
		$where = array_merge($where,  array( 'isAnAbuse' => array('$ne' => true) ) );
		$where = array_merge($where,  array('sharedBy.updated' => array( '$lt' => $date ) ) );
		$where = array_merge($where, array("target.type" => array('$ne' => "pixels")));
		if(@$_POST["search"]){
			$searchIn=$_POST["search"];
			$params["localities"] = (@$searchIn['locality']) ? $searchIn['locality'] : null;
	  		$allQueryLocality = array();
	  		if(!empty($params["localities"])){
	  			foreach ($params["localities"] as $key => $locality){
					if(!empty($locality)){

						if($locality["type"] == City::COLLECTION){
							$queryLocality = array("scope.localities.parentId" => $locality["id"], "scope.localities.parentType" =>  $locality["type"]);
							if(!empty($locality["postalCode"]))
								$queryLocality = array_merge($queryLocality, array("scope.localities.postalCode" => new MongoRegex("/^".$locality["postalCode"]."/i")));
						}
						else if($locality["type"] == "cp")
							$queryLocality = array("scope.localities.postalCode" => new MongoRegex("/^".$locality["name"]."/i"));
						else
							$queryLocality = array('$or'=>array(
								array("scope.localities.parentId" => $locality["id"]),
								array("scope.localities.".$locality["type"] => $locality["id"]))
						);
					
						if(empty($allQueryLocality))
							$allQueryLocality = $queryLocality;
						else if(!empty($queryLocality))
							$allQueryLocality = array('$or' => array($allQueryLocality ,$queryLocality));
					}
				}
	  		}
	  		if(@$allQueryLocality){
				$where = array_merge($where, $allQueryLocality);
			}
			if(@$searchIn["searchTags"] && !empty($searchIn["searchTags"])){
				$queryTag = array();
				foreach ($searchIn["searchTags"] as $key => $tag) {
					if($tag != "")
					$queryTag[] = new MongoRegex("/".$tag."/i");
				}

				if(!empty($queryTag))
				$where["tags"] = array('$in' => $queryTag); 			
			}

			
			if(@$searchIn['searchType']){
				$searchType=array();
				foreach($searchIn['searchType'] as $data){
					if($data == "news")
						$searchType=array("type" => $data);
					else if ($data == "activityStream")
						$searchType=array("type" => $data);
					else if($data == "surveys")
						$searchType=array("object.type"=>"proposals", "verb"=>"publish");
				}
				if(!empty($searchType))
					$where = array_merge($where, $searchType);
			}
			if(@$searchIn["name"] && $searchIn["name"]!=""){
			
				$textTag=null;
		  		$textSearch = $searchIn["name"];
		  		$textTag = explode(" ", $textSearch);
				$hashTag = substr($textSearch, 0, 1);
		  		if(sizeof($textTag)==1 && $hashTag=="#"){
		  			$tagClear = substr($textTag[0], 1, strlen($textTag[0])-1);
		  			$textTag = array($textTag[0], $tagClear);
		  			$where["tags"] = array('$in' => $textTag); 		
		  		}else{
					$where = array_merge($where,  array('text' => new MongoRegex("/".$textSearch."/i") ) );
		  		}
		  	
				
			}
		}
		if(!empty($where)){
			$limit=(@$_POST["indexStep"]) ? $_POST["indexStep"] : 6; 
			$news= News::getNewsForObjectId($where,array("sharedBy.updated"=>-1),$type, $limit, @$followsArrayIds);
			$params["endStream"]=(count($news) == $limit) ? false : true;
			foreach ($news as $key => $newsItem) {
			    if(@$newsItem["type"]){
				    $newNews=NewsTranslator::convertParamsForNews($newsItem, false, @$followsArrayIds);
				    if(!empty($newNews))			  		
						$news[$key]=$newNews;
					else
						unset($news[$key]);
		  		}
		  	}
		 }
		// Sort news order by created 
		$news = News::sortNews($news, array('updated'=>SORT_DESC));

		//remove activityStream if user connected can't access his parentRoom (because of room role access)
		$news = Cooperation::checkRoleAccessInNews($news);

        //TODO : reorganise by created date
		$params["news"] = $news;
		$params["limitDate"] = end($news);

		if(@$parent){
			$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $parent["_id"]);
			$params["openEdition"] = Authorisation::isOpenEdition($parent["_id"], $type, @$parent["preferences"]);
		}else{
			$params["edit"] = false;
			$params["openEdition"] = false;
		}
		
		$params["nbCol"] = (@$_POST["nbCol"]) ? $_POST["nbCol"] : 1;
		$params["inline"] = (@$_POST["inline"]) ? $_POST["inline"] : false;
		//manage delete in progress status
		if (in_array($type,[Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION, Person::COLLECTION]))
			$params["deletePending"] = Element::isElementStatusDeletePending($type, $id);

		if(@$json)
			echo json_encode($params);
		else
			echo $controller->renderPartial("news.views.co.timelineTree", $params,true);
    }
}