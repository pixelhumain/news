/* ******************
CO.js
********************* */

function escapeHtml(string) {
	var entityMap = {
	    '"': '&quot;',
    	"'": '&#39;',
	};
    return String(string).replace(/["']/g, function (s) {
        return entityMap[s];
    });
} 
/* *********************************
			COLLECTIONS
********************************** */

var collection = {
	crud : function (action, name,type,id) { 
		if(userId){
			var params = {};
			var sure = true;
			if(typeof type != "undefined")
				params.type = type;
			if(typeof id != "undefined")
				params.id = id;
			if(typeof action == "undefined")
				action = "new";
			if(action == "del"){
				params.name = name;
				sure = confirm("Vous êtes sûr ?");
			}
			else if(action == "new" || action == "update")
				params.name = prompt(tradDynForm.collectionname+' ?',name);
			if(action == "update")
				params.oldname = name;
			
			if(sure)
			{
				ajaxPost(null,baseUrl+"/"+moduleId+"/collections/crud/action/"+action ,params,function(data) { 
					console.warn(params.action);
					if(data.result){
						toastr.success(data.msg);
						if(location.hash.indexOf("#page") >=0){
							loadDataDirectory("collections", "star");
						}
						//if no type defined we are on user
						//TODO : else add on the contextMap
						if( typeof type == "undefined" && action == "new"){
							if(!userConnected.collections)
								userConnected.collections = {};
							if(!userConnected.collections[params.name])
								userConnected.collections[params.name] = {};
						}else if(action == "update"){
							smallMenu.openAjax(baseUrl+'/'+moduleId+'/collections/list/col/'+params.name,params.name,'fa-folder-open','yellow');
							if(!userConnected.collections[params.name])
								userConnected.collections[params.name] = userConnected.collections[ params.oldname ];
							delete userConnected.collections[ params.oldname ];
						}else if(action == "del"){
							delete userConnected.collections[params.name];
							smallMenu.open();
						}
						collection.buildCollectionList("col_Link_Label_Count",".menuSmallBtns", function() { $(".collection").remove() })
					}
					else
						toastr.error(data.msg);
				}, "none");
			}
		} else
			toastr.error(trad.LoginFirst);
	},
	applyColor : function (what,id,col) {
		var collection = (typeof col == "undefined") ? "favorites" : col;
		if(userConnected && userConnected.collections && userConnected.collections[collection] && userConnected.collections[collection][what] && userConnected.collections[collection][what][id] ){
			$(".star_"+what+"_"+id).children("i").removeClass("fa-star-o").addClass('fa-star text-red');
		}
	},
	isFavorites : function (type, id){
		res=false;
		if(userConnected && userConnected.collections){
			$.each(userConnected.collections, function(name, listCol){
				if(typeof listCol[type] != "undefined" && typeof listCol[type][id] != "undefined"){
					res=name;
					return false;
				}
			});
		}
		return res;
	},
	add2fav : function (what,id,col){
		var collection = (typeof col == "undefined") ? "favorites" : col;
		if(userId){
			var params = { id : id, type : what, collection : collection };
			var el = ".star_"+what+"_"+id;
			
			ajaxPost(null,baseUrl+"/"+moduleId+"/collections/add",params,function(data) { 
				console.warn(params.action,collection,what,id);
				if(data.result){
					if(data.list == '$unset'){
						/*if(location.hash.indexOf("#page") >=0){
							if(location.hash.indexOf("view.directory.dir.collections") >=0 && contextData.id==userId){ 
                				loadDataDirectory("collections", "star"); 
              				}else{ 
                				$(".favorisMenu").removeClass("text-yellow"); 
                				$(".favorisMenu").children("i").removeClass("fa-star").addClass('fa-star-o'); 
              				} 
						}else{*/
							$(el).removeClass("letter-yellow-k"); 
							$(el).find("i").removeClass("fa-star letter-yellow-k").addClass('fa-star-o');
							delete userConnected.collections[collection][what][id];
						//}
					}
					else{
						/*if(location.hash.indexOf("#page") >=0){
							if(location.hash.indexOf("view.directory.dir.collections") >=0 && contextData.id==userId){ 
                				loadDataDirectory("collections", "star"); 
              				}else{ 
                				$(".favorisMenu").addClass("text-yellow"); 
                				$(".favorisMenu").children("i").removeClass("fa-star-o").addClass('fa-star'); 
              				}
              			}
						else*/
							$(el).addClass("letter-yellow-k"); 
							$(el).find("i").removeClass("fa-star-o").addClass('fa-star letter-yellow-k');

						if(!userConnected.collections)
							userConnected.collections = {};
						if(!userConnected.collections[collection])
							userConnected.collections[collection] = {};
						if(!userConnected.collections[collection][what])
							userConnected.collections[collection][what] = {};
						userConnected.collections[collection][what][id] = new Date();	
					}
					toastr.success(data.msg);
				}
				else
					toastr.error(data.msg);
			},"none");
		} else
			toastr.error(trad.LoginFirst);
	},
	buildCollectionList : function ( tpl, appendTo, reset ) {
		if(typeof reset == "function")
			reset();
		str = "";
		$.each(userConnected.collections, function(col,list){ 
			var colcount = 0;
			$.each(list, function(type,entries){
				colcount += Object.keys(entries).length;
			}); 
			str += js_templates[ tpl ]({
				label : col,
				labelCount : colcount
			}) ;
		});
		$(appendTo).append(str);
	}
};

var mentionsInput=[];
var mentionsInit = {
	stopMention : false,
	isSearching : false,
	get : function(domElement){
		mentionsInput=[];
		$(domElement).mentionsInput({
			allowRepeat:true,
		 	onDataRequest:function (mode, query, callback) {
			  	if(!mentionsInit.stopMention){
				  	var data = mentionsContact;
				  	data = _.filter(data, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
					callback.call(this, data);
					mentionsInit.isSearching=true;
			   		var search = {"searchType" : ["citoyens","organizations","projects"], "name": query};
			  		$.ajax({
						type: "POST",
				        url: baseUrl+"/"+moduleId+"/search/globalautocomplete",
				        data: search,
				        dataType: "json",
				        success: function(retdata){
				        	if(!retdata){
				        		toastr.error(retdata.content);
				        	}else{
					        	mylog.log(retdata);
					        	data = [];
					        	//for(var key in retdata){
						        //	for (var id in retdata[key]){
						        $.each(retdata.results, function (e, value){
							        	avatar="";
							        	//console.log(retdata[key]);
							        	//aert(retdata[key][id].type);
							        	if(typeof value.profilThumbImageUrl != "undefined" && value.profilThumbImageUrl!="")
							        		avatar = baseUrl+value.profilThumbImageUrl;
							        	object = new Object;
							        	object.id = e;
							        	object.name = value.name;
							        	object.slug = value.slug;
							        	object.avatar = avatar;
							        	object.type = value.type;
							        	var findInLocal = _.findWhere(mentionsContact, {
											name: value.name, 
											type: value.type
										}); 
										if(typeof(findInLocal) == "undefined"){
											mentionsContact.push(object);
										}
							 	//		}
					        	//}
					        	});
					        	data=mentionsContact;
					    		data = _.filter(data, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
								callback.call(this, data);
								mylog.log("callback",callback);
				  			}
						}	
					});
			  	}
			 }
	  	});
	},
	beforeSave : function(object, domElement){
		$(domElement).mentionsInput('getMentions', function(data) {
			mentionsInput=data;
		});
		if (typeof mentionsInput != "undefined" && mentionsInput.length != 0){
			var textMention="";
			$(domElement).mentionsInput('val', function(text) {
				textMention=text;
				console.log(textMention);
				$.each(mentionsInput, function(e,v){
					strRep=v.name;
					while (textMention.indexOf("@["+v.name+"]("+v.type+":"+v.id+")") > -1){
						if(typeof v.slug != "undefined")
							strRep="@"+v.slug;
						textMention = textMention.replace("@["+v.name+"]("+v.type+":"+v.id+")", strRep);
					}
				});
			});			
			object.mentions=mentionsInput;
			object.text=textMention;
		}
		return object;		      		
	},
	addMentionInText: function(text,mentions){
		$.each(mentions, function( index, value ){
			if(typeof value.slug != "undefined"){
				while (text.indexOf("@"+value.slug) > -1){
					str="<span class='lbh' onclick='urlCtrl.loadByHash(\"#page.type."+value.type+".id."+value.id+"\")' onmouseover='$(this).addClass(\"text-blue\");this.style.cursor=\"pointer\";' onmouseout='$(this).removeClass(\"text-blue\");' style='color: #719FAB;'>"+
			   						value.name+
			   					"</span>";
					text = text.replace("@"+value.slug, str);
				}
			}else{
				//Working on old news
		   		array = text.split(value.value);
		   		text=array[0]+
		   					"<span class='lbh' onclick='urlCtrl.loadByHash(\"#page.type."+value.type+".id."+value.id+"\")' onmouseover='$(this).addClass(\"text-blue\");this.style.cursor=\"pointer\";' onmouseout='$(this).removeClass(\"text-blue\");' style='color: #719FAB;'>"+
		   						value.name+
		   					"</span>"+
		   				array[1];
		   	}   					
		});
		return text;
	},
	reset: function(domElement){
		$(domElement).mentionsInput('reset');
	}
}

var smallMenu = {
	destination : ".menuSmallBlockUI",
	inBlockUI : true,
	//smallMenu.openAjax(\''+baseUrl+'/'+moduleId+'/collections/list/col/'+obj.label+'\',\''+obj.label+'\',\'fa-folder-open\',\'yellow\')
	//the url must return a list like userConnected.list
	openAjax : function  (url,title,icon,color,title1,params,callback) 
	{ 
		/*if( typeof directory == "undefined" )
		    lazyLoad( moduleUrl+'/js/default/directory.js', null, null );
	    */
	    //processingBlockUi();
	    //$(smallMenu.destination).html("<i class='fa fa-spin fa-refresh fa-4x'></i>");

		ajaxPost( null , url, params , function(data)
		{
			if(!title1 && notNull(title1) && data.context && data.context.name)
				title1 = data.context.name;
			var content = smallMenu.buildHeader( title,icon,color,title1 );
			smallMenu.open( content );
			if( data.count == 0 )
				$(".titleSmallMenu").html("<a class='text-white' href='javascript:smallMenu.open();'> <i class='fa fa-th'></i></a> "+	
						' <i class="fa fa-angle-right"></i> '+
						title+" vide <i class='fa "+icon+" text-"+color+"'></i>");
			else 
				directory.buildList(data.list);
			
		   	$('.searchSmallMenu').off().on("keyup",function() { 
				directory.search ( ".favSection", $(this).val() );
		   	});
		   	//else collection.buildCollectionList( "linkList" ,"#listCollections",function(){ $("#listCollections").html("<h4 class=''>Collections</h4>"); });

		   	if (typeof callback == "function") 
				callback(data);
	    } );
	},
	build : function  (params,build_func,callback) { 
		//processingBlockUi();
	   	if (typeof build_func == "function") 
			content = build_func(params);
		smallMenu.open( content );
		if (typeof callback == "function") 
			callback();
	},
	//ex : smallMenu.openSmall("Recherche","fa-search","green",function(){
	openSmall : function  (title,icon,color,callback,title1) { 
		if( typeof directory == "undefined" )
		    lazyLoad( moduleUrl+'/js/default/directory.js', null, null );
	    	
		var content = smallMenu.buildHeader(title,icon,color,title1);
		smallMenu.open( content );

		if (typeof callback == "function") 
			callback();
	},
	buildHeader : function (title,icon,color,title1) { 
		title1 = (typeof title1 != "undefined" && notNull(title1)) ? title1 : "<a class='text-white' href='javascript:smallMenu.open();'> <i class='fa fa-th'></i></a> ";
		content = 
				"<div class='col-xs-12 padding-5'>"+

					"<h3 class='titleSmallMenu'> "+
						title1+"<i class='fa "+icon+" text-"+color+"'></i> "+title+
					"</h3><hr>"+
					"<div class='col-md-12 bold sectionFilters'>"+
						"<a class='text-black bg-white btn btn-link favSectionBtn btn-default' "+
							"href='javascript:directory.toggleEmptyParentSection(\".favSection\",null,\".searchEntityContainer\",1)'>"+
							"<i class='fa fa-asterisk fa-2x'></i><br>Tout voir</a></span> </span>"+
					"</div>"+

					"<div class='col-md-12'><hr></div>"+

				"</div>"+

				"<div id='listDirectory' class='col-md-10 no-padding'></div>"+
				"<div class='hidden-xs col-sm-2 text-left'>"+
					"<input name='searchSmallMenu' style='border:1px solid red' class='form-control searchSmallMenu text-black' placeholder='rechercher' style=''><br/>"+
					"<h4 class=''><i class='fa fa-angle-down'></i> Filtres</h4>"+
					"<a class='btn btn-dark-blue btn-anc-color-blue btn-xs favElBtn favAllBtn text-left' href='javascript:directory.toggleEmptyParentSection(\".favSection\",null,\".searchEntityContainer\",1)'> <i class='fa fa-tags'></i> Tout voir </a><br/>"+

					"<div id='listTags'></div>"+
					"<div id='listScopes'><h4><i class='fa fa-angle-down'></i> Où</h4></div>"+
					"<div id='listCollections'></div>"+
				"</div> "+
				"<div class='col-xs-12 col-sm-10 center no-padding'>"+
					//"<a class='pull-right btn btn-xs btn-default' href='javascript:collection.newChild(\""+title+"\");'> <i class='fa fa-sitemap'></i></a> "+
					"<a class='pull-right btn btn-xs menuSmallTools hide text-red' href='javascript:collection.crud(\"del\",\""+title+"\");'> <i class='fa fa-times'></i></a> "+
					"<a class='pull-right btn btn-xs menuSmallTools hide'  href='javascript:collection.crud(\"update\",\""+title+"\");'> <i class='fa fa-pencil'></i></a> "+
					
					// "<h3 class='titleSmallMenu'> "+
					// 	title1+' <i class="fa fa-angle-right"></i> '+title+" <i class='fa "+icon+" text-"+color+"'></i>"+
					// "</h3>"+
					// "<input name='searchSmallMenu' class='searchSmallMenu text-black' placeholder='rechercher' style='margin-bottom:8px;width: 300px;font-size: x-large;'><br/>"+
					
				"</div>";
		return content;
	},
	ajaxHTML : function (url,title,type,nextPrev) { 
		var dest = (type == "blockUI") ? ".blockContent" : "#openModal .modal-content .container" ;
		getAjax( dest , url , function () { 
			
			//next and previous btn to nav from preview to preview
			if(nextPrev){
				var p = 0;
				var n = 0;
				var found = false;
				var l = $( '.searchEntityContainer .container-img-profil' ).length;
				$.each( $( '.searchEntityContainer .container-img-profil' ), function(i,val){
					if(found){
						n = (i == l-1 ) ? $( $('.searchEntityContainer .container-img-profil' )[0] ).attr('href') : $(this).attr('href');
						return false;
					}
					if( $(this).attr('href') == nextPrev )
						found = true;
					else 
						p = (i == 0 ) ? $( $('.searchEntityContainer .container-img-profil' )[ $('.searchEntityContainer .container-img-profil' ).length ] ).attr('href') : $(this).attr('href');
				});
				html = "<div style='margin-bottom:50px'><a href='"+p+"' class='lbhp text-dark'><i class='fa fa-2x fa-arrow-circle-left'></i> PREV </a> "+
						" <a href='"+n+"' class='lbhp text-dark'> NEXT <i class='fa fa-2x fa-arrow-circle-right'></i></a></div>";
				$(dest).prepend(html);
				
			}
			bindLBHLinks();
		 },"html" );
	},
	//openSmallMenuAjaxBuild("",baseUrl+"/"+moduleId+"/favorites/list/tpl/directory2","FAvoris")
	//opens any html without post processing
	openAjaxHTML : function  (url,title,type,nextPrev) { 
		smallMenu.open("",type );
		smallMenu.ajaxHTML(url,title,type,nextPrev);
	},
	//content Loader can go into a block
	//smallMenu.open("Recherche","blockUI")
	//smallMenu.open("Recherche","bootbox")
	open : function (content,type,color,callback) { 
		//alert("small menu open");
		//add somewhere in page
		if(!smallMenu.inBlockUI){
			$(smallMenu.destination).html( content );
			$.unblockUI();
		}
		else {
			//this uses blockUI
			if(type == "blockUI"){
				colorCSS = (color == "black") ? 'rgba(0,0,0,0.70)' : 'rgba(256,256,256,0.85)';
				colorText = (color == "black") ? '#fff' : '#000';
				$.blockUI({ 
					//title : 'Welcome to your page', 
					message : (content) ? content : "<div class='blockContent'></div>",
					onOverlayClick: $.unblockUI(),
			        css: { 
			         //border: '10px solid black', 
			         //margin : "50px",
			         //width:"80%",
			         //    padding: '15px', 
			         backgroundColor: colorCSS,  
			         //    '-webkit-border-radius': '10px', 
			         //    '-moz-border-radius': '10px', 
			             color: colorText ,
			        	// "cursor": "pointer"
			        }//,overlayCSS: { backgroundColor: '#fff'}
				});
			}else if(type == "bootbox"){
				bootbox.dialog({
				  message: content
				});
			} else{//open inside a boostrap modal 
				if(!$("#openModal").hasClass('in'))
					$("#openModal").modal("show");
				if(content)
					smallMenu.content(content);
				else 
					smallMenu.content("<i class='fa fa-spin fa-refresh fa-4x'></i>");
			}

			$(".blockPage").addClass(smallMenu.destination.slice(1));
			// If network, check width of menu small
			if( typeof globalTheme != "undefined" && globalTheme == "network" ) {
				if($("#ficheInfoDetail").is(":visible"))
					$(smallMenu.destination).css("cssText", "width: 100% !important;left: 0% !important;");
				else
					$(smallMenu.destination).css("cssText", "width: 83.5% !important;left: 16.5% !important;");
			}
			bindLBHLinks();
			if (typeof callback == "function") 
				callback();
		}
	},
	content : function(content) { 
		el = $("#openModal div.modal-content div.container");
		if(content == null)
			return el;
		else
			el.html(content);
	}
};