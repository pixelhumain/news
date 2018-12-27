<?php 
  $contextScopeNews=array(
      Organization::COLLECTION => array(
      "public"=>array(
        "icon"=>"globe",
        "label"=>ucfirst(Yii::t("common", "public")),
        "explain"=>Yii::t("news","Write a public message visible in live on selected places")
      ),
      "restricted"=>array(
        "icon"=>"rss",
        "label"=>ucfirst(Yii::t("common", "followers")),
        "explain"=>Yii::t("news", "Posted on followers and members wall and visible to all on this wall")
      ),
      "private"=>array(
        "icon"=>"users",
        "label"=>ucfirst(Yii::t("common", "members")),
        "explain"=>Yii::t("news", "Posted on members wall and visible only for them")
      ),
      "init"=>array(
        "admin"=>"restricted",
        "noadmin"=>"private"
      )
      ),
      Project::COLLECTION => array(
          "public"=>array(
            "icon"=>"globe",
            "label"=>ucfirst(Yii::t("common", "public")),
            "explain"=>Yii::t("news","Write a public message visible in live on selected places")
          ),
          "restricted"=>array(
            "icon"=>"rss",
            "label"=>ucfirst(Yii::t("common", "followers")),
            "explain"=>Yii::t("news", "Posted on followers and contributors wall and visible to all on this wall")
          ),
          "private"=>array(
            "icon"=>"users",
            "label"=>ucfirst(Yii::t("common", "contributors")),
            "explain"=>Yii::t("news", "Posted on contributors wall and visible only for them")
          ),
          "init"=>array(
            "admin"=>"restricted",
            "noadmin"=>"private"
          )
      ),
      Event::COLLECTION => array(
          "public"=>array(
            "icon"=>"globe",
            "label"=>ucfirst(Yii::t("common", "public")),
            "explain"=>Yii::t("news","Write a public message visible in live on selected places")
          ),
        "restricted"=>array(
            "icon"=>"rss",
            "label"=>ucfirst(Yii::t("common", "attendees")),
            "explain"=>Yii::t("news", "Posted on attendees wall and visible to all on this wall"),
          ),
          "private"=>array(
            "icon"=>"users",
            "label"=>ucfirst(Yii::t("common", "admins")),
            "explain"=>Yii::t("news","Posted on administrators wall and visible only for them"),
          ),
          "init"=>array(
            "admin"=>"restricted",
            "noadmin"=>"private"
        )
      ),
      Person::COLLECTION => array(
        "public"=>array(
            "icon"=>"globe",
            "label"=>ucfirst(Yii::t("common", "public")),
            "explain"=>Yii::t("news","Write a public message visible in live on selected places")
          ),
          "restricted"=>array(
            "icon"=>"rss",
            "label"=>ucfirst(Yii::t("common", "followers")),
            "explain"=>Yii::t("news", "Posted on followers wall and visible to all on this wall"),
          ),
          "private"=>array(
            "icon"=>"lock",
            "label"=>ucfirst(Yii::t("common", "private")),
            "explain"=>Yii::t("news","Visible only to me"),
          ),
          "init"=>array(
            "admin"=>"restricted",
            "noadmin"=>"private"
          )
      ),
      "city" => array(
        "public"=>array(
            "icon"=>"globe",
            "label"=>ucfirst(Yii::t("common", "public")),
            "explain"=>Yii::t("news","Write a public message visible in live on selected places"),
          )
      )
    );//News::$newsTypeContext;
  $isLive = isset($_GET["isLive"]) ? true : false;
  $contextName = @$parent["name"];
  $contextIcon = "bookmark fa-rotate-270";
  $contextTitle = "";
  $imgProfil = $this->module->assetsUrl . "/images/news/profile_default_l.png"; 
  $textForm = Yii::t("common","Write a public message visible on the wall of selected places");
  $imgProfil = "";
  $sizeForm=(!empty($contextParentType) && $contextParentType != "city") ? "col-xs-12" : "col-xs-12 col-sm-10 col-sm-offset-1"; 

?>
<!-- <div id="newLiveFeedForm" class="col-xs-12 no-padding margin-bottom-10"></div> -->

<?php if(isset(Yii::app()->session['userId'])){ ?>
  <div id="formCreateNewsTemp" style="float: none;" class="center-block hidden">
    <div class='no-padding form-create-news-container <?php echo $sizeForm ?>'>

      <div class='padding-10 partition-light no-margin text-left header-form-create-news'>
        <i class='fa fa-angle-down'></i> <i class="fa fa-file-text-o"></i> <span id="info-write-msg"><?php echo $textForm; ?></span>
        <a class="btn btn-xs pull-right" style="margin-top: -4px;" onclick="javasctipt:showFormBlock(false);">
          <i class="fa fa-times"></i>
        </a>

        <?php  //if($type != City::CONTROLLER){ //si on est pas sur le live, on met le bouton "creer sondage" ?>
        <button onclick="dyFObj.openForm('survey')" class="btn btn-link btn-xs bold letter-light hidden-xs pull-right margin-right-5"
                style="margin-top: -3px;">
                <i class="fa fa-plus-circle"></i> <?php echo Yii::t("cooperation", "create a survey") ?>
        </button>
        <?php //} ?>

        <button onclick='showFormBlock(true);dataHelper.activateMarkdown("#get_url");' class="btn btn-link btn-xs bold letter-light hidden-xs pull-right margin-right-5"
                style="margin-top: -3px;">
                <i class="fa fa-list-ul"></i> Markdown
        </button>

      </div>

      <div class="tools_bar bg-white">
        <?php if((@$canManageNews && $canManageNews==true) || (@$isLive && $isLive == true)){ ?>
        <div class="user-image-buttons">
          <form method="post" id="photoAddNews" enctype="multipart/form-data">
            <span class="btn btn-white btn-file fileupload-new btn-sm uploadImageNews"  <?php //if (!$authorizedToStock){ echo 'onclick="addMoreSpace();"'; } ?>>
            <span class="fileupload-new"><i class="fa fa-picture-o fa-x"></i> </span>
              <?php //if ($authorizedToStock){ ?>
                <input type="file" accept=".gif, .jpg, .png" name="newsImage" id="addImage" onchange="showMyImage(this);">
              <?php //} ?>
            </span>
          </form>
        </div>
        <?php } ?>
      </div>

      <div id='form-news' class="col-sm-12 no-padding">
        
        <input type="hidden" id="parentId" name="parentId" 
               value="<?php if($contextParentType != "city") echo $contextParentId; else echo Yii::app()->session["userId"]; ?>"/>
        <input type="hidden" id="parentType" name="parentType" 
               value="<?php if($contextParentType != "city") echo $contextParentType; else echo Person::COLLECTION; ?>"/> 
        
        <input type="hidden" id="typeNews" name="type" value="news"/>

        <input  type="text" id="falseInput" onclick="javascript:showFormBlock(true);" 
            class="col-xs-12 col-md-12" placeholder="<?php echo Yii::t("common","Express yourself ...") ?>" style="padding:15px;"/>

        <div class="extract_url" style="display:none;">
          <div class="padding-10 bg-white">
            <img class="loading_indicator" src="<?php echo $this->module->assetsUrl ?>/images/ajax-loader.gif">
            <textarea id="get_url" placeholder="<?php echo Yii::t("common","Express yourself ...") ?>" class="get_url_input form-control textarea mention" style="border:none;background:transparent !important" name="getUrl" spellcheck="false" ></textarea>
            <ul class="dropdown-menu" id="dropdown_search" style="">
            </ul>

            <div id="results" class="bg-white results col-sm-12 padding-10"></div>
          </div>
        </div>
        <div class="form-group tagstags col-md-12 col-sm-12 col-xs-12 no-padding">
            <input id="tags" type="" data-type="select2" name="tags" placeholder="#Tags" value="" style="width:100%;">
        </div>
        
        <div id="scopeListContainerForm" class="form-group col-md-12 col-sm-12 col-xs-12 no-padding margin-bottom-10"></div>
        <div class="form-actions col-md-12 col-sm-12 col-xs-12 no-padding" style="display: block;">
          
          

          <div class="col-md-12 col-sm-12 col-xs-12 no-padding">
            <hr class="submit">
            
            <button id="btn-submit-form" onclick="saveNews();" class="btn btn-success pull-right">
              <?php echo Yii::t("common","Submit") ?> <i class="fa fa-arrow-circle-right"></i>
            </button>


          <?php if((@$canManageNews && $canManageNews==true) 
                || (@Yii::app()->session["userId"] 
                && $contextParentType==Person::COLLECTION 
                && Yii::app()->session["userId"]==$contextParentId)){ ?>
          <div class="dropdown col-md-6 no-padding">
            <a data-toggle="dropdown" class="btn btn-default" id="btn-toogle-dropdown-scope" href="#"><i class="fa fa-<?php echo @$contextScopeNews[$contextParentType][$contextScopeNews[$contextParentType]["init"]["admin"]]["icon"] ?>"></i> <?php echo @$contextScopeNews[$contextParentType][$contextScopeNews[$contextParentType]["init"]["admin"]]["label"] ?> <i class="fa fa-caret-down" style="font-size:inherit;"></i></a>
            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
              <?php if(@$contextScopeNews && @$contextScopeNews[$contextParentType]){
                  foreach($contextScopeNews[$contextParentType] as $key => $v){
                    if($key != "init"){ ?>
                      <li>
                        <a href="javascript:;" class="scopeShare" data-value="<?php echo $key ?>"><h4 class="list-group-item-heading"><i class="fa fa-<?php echo $v["icon"] ?>"></i> <?php echo $v["label"] ?></h4>
                          <p class="list-group-item-text small"><?php echo $v["explain"] ?></p>
                        </a>
                      </li>
                    <?php } 
                  }
                } ?>
            </ul>
          </div>  

          
          <?php if($contextParentType == Organization::COLLECTION || $contextParentType == Project::COLLECTION || $contextParentType == Event::COLLECTION){ ?>
          <div class="dropdown no-padding pull-right">
            <a data-toggle="dropdown" class="btn btn-default" id="btn-toogle-dropdown-targetIsAuthor" href="#">
              <?php if(@Yii::app()->session["user"]["profilThumbImageUrl"]){ ?>
              <img height=20 width=20 src='<?php echo Yii::app()->getRequest()->getBaseUrl(true).Yii::app()->session["user"]["profilThumbImageUrl"]; ?>'>
              <?php } else {  ?>
                <img height=20 width=20 src='<?php echo $this->module->assetsUrl.'/images/thumb/default_citoyens.png' ?>'>  
              <?php } ?>
              <i class="fa fa-caret-down" style="font-size:inherit;"></i>
            </a>
            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
              <li>
                <a href="javascript:;" class="targetIsAuthor" data-value="1">
                  <h4 class="list-group-item-heading">
                  <?php if(@$parent["profilThumbImageUrl"]){ ?>
                    <img height=20 width=20 src='<?php echo Yii::app()->getRequest()->getBaseUrl(true).$parent["profilThumbImageUrl"] ?>'>
                  <?php } else { ?>
                    <img height=20 width=20 src='<?php echo $this->module->assetsUrl.'/images/thumb/default_'.$contextParentType.'.png' ?>'>  
                  <?php } ?>
                  <?php echo $contextName ?></h4>
                  <p class="list-group-item-text small"><?php echo Yii::t("form", "Show {who} as author",array("{who}"=>$contextName)) ?></p>
                </a>
              </li>
              <li>
                <a href="javascript:;" class="targetIsAuthor" data-value="0"><h4 class="list-group-item-heading">
                  <?php if(@Yii::app()->session["user"]["profilThumbImageUrl"]){ ?>
                  <img height=20 width=20 src='<?php echo Yii::app()->getRequest()->getBaseUrl(true).Yii::app()->session["user"]["profilThumbImageUrl"]; ?>'>
                  <?php } else {  ?>
                    <img height=20 width=20 src='<?php echo $this->module->assetsUrl.'/images/thumb/default_citoyens.png' ?>'>  
                  <?php } ?>
                  <?php echo ucfirst(Yii::t("common", "Me")) ?></h4>
                  <p class="list-group-item-text small"><?php echo Yii::t("form","I am the author") ?></p>
                </a>
              </li>
            </ul>
            <input type="hidden" id="authorIsTarget" value="0"/>
          </div>  
            <?php } ?>    
          <?php } ?>



          <?php if(!@$contextParentType || $contextParentType=="city"){ ?>
            <input type="hidden" name="scope" value="public"/>
          <?php } else if((@$canManageNews && $canManageNews=="true") || (
              @Yii::app()->session["userId"] && 
              $contextParentType==Person::COLLECTION && Yii::app()->session["userId"]==$contextParentId)){
                if(in_array($contextParentType,array(Event::COLLECTION,Person::COLLECTION,Project::COLLECTION,Organization::COLLECTION))){ ?>
                  <input type="hidden" name="scope" value="restricted"/>
               <?php } else { ?>
                  <input type="hidden" name="scope" value="public"/>
              <?php } 

            }else if($contextParentType==Event::COLLECTION){?>
            
            <input type="hidden" name="scope" value="restricted"/>

          <?php } else { ?>

            <input type="hidden" name="scope" value="private"/>

          <?php } ?>
          </div>
        </div>
      </div>
     </div>
  </div>
<?php }else{ ?>
  <div class="col-xs-12 text-center font-montserrat">
    <hr>
    <h5 class="letter-red">
      <i class="fa fa-ban"></i> <?php echo Yii::t("cooperation","You are not logged"); ?><br>
      <small><?php echo Yii::t("cooperation","Please login to post a message"); ?></small>
      <br><br>

      <button class="btn btn-link bg-green-k" data-toggle="modal" data-target="#modalLogin">
        <i class="fa fa-sign-in"></i> <?php echo Yii::t("cooperation","I'm logging in"); ?>
      </button>
      <button class="btn btn-link bg-blue-k margin-left-10" data-toggle="modal" data-target="#modalRegister">
        <i class="fa fa-plus-circle"></i> <?php echo Yii::t("cooperation","I create my account"); ?>
      </button> 
      <br><br>
      <small class="letter-blue"><i class="fa fa-check"></i> <?php echo Yii::t("cooperation","free registration"); ?></small>
    </h5>
    <hr>
  </div>
<?php } ?>
<script type="text/javascript">
  var contextScopeNews = <?php echo json_encode($contextScopeNews) ?>;
</script>