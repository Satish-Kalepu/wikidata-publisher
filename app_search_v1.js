/*
made by satish on 1st april 2022
api response should have

API URL
api.php/?action=satish_product_search&keyword=##KEYWORD##&limit=##LIMIT##

response should be application/json
{
	"status": "success",
	"keywords": [
		{
		"id": int
		"keyword": "something"
		},
		......
	]
}

*/

function create_search_app_v1( vops ){

	console.log( vops );
	console.log( ('url' in vops?vops['url']:"") );

	console.log( vops['select_event'] )

	var new_app = new Vue({
	el: "#"+('mount_to' in vops?vops['mount_to']:""),
	data: {
		dest_hidden_input_id: ('hidden_field' in vops?vops['hidden_field']:""),
		select_event: ('select_event' in vops?vops['select_event']:false),
		keyword: "",
		keywords: [],
		keywords_: {},
		suggestions: [],
		keys:{},
		t: -1,
		limit: 100,
		kpr: new RegExp(),
		key: "",
		isbusy: false,
		vfocused: false,
		ft: -1,
		vselected: false,
		api_url: ('url' in vops?vops['url']:"/url_undefined"),
		display_text: ('display_text' in vops?vops['display_text']:"Click to Search"),
		vshow: false,
		vrand: "rand_" + parseInt(Math.random()*100000),
	},
	mounted: function(){
		// this.load_
	},
	methods: {
		showit: function(){
			this.vshow=true;
			setTimeout(this.setfocus,500);
		},
		setfocus: function(){
			this.$refs['input'].focus();
		},
		selecte: function(i){
			this.vshow = false;
			this.vselected = true;
			this.display_text = i.split("#")[0];
			if( document.getElementById(this.dest_hidden_input_id) == undefined ){
				console.error( "Element: "+this.dest_hidden_input_id + " not found" );
			}else{
				document.getElementById( this.dest_hidden_input_id ).value = i.split("#")[1];
				eval( this.select_event + "('"+ i.split("#")[1] +"')" );
			}
		},
		getid: function(v){
			return v.split("#")[1];
		},
		ismatch1: function(v){
			var k = this.keyword.trim().replace(/\W+/g, ".*");
			var kpr = new RegExp(k,"i");
			var t = v.split("#")[0];
			if( t.match(kpr) ){
				return true;
			}else{
				return false;
			}
		},
		ismatch2: function(v){
			var k = this.keyword.trim().replace(/\W+/g, ".*");
			var kpr = new RegExp(k,"i");
			var t = v.split("#")[0];
			if( t.match(kpr) ){
				return true;
			}else{
				return false;
			}
		},
		vformat: function(vv){
			var v = vv.split("#")[0]+'';
			var k = this.keyword.trim().split(/\W+/g);
			for(var i=0;i<k.length;i++){
				var rg = new RegExp( k[i], "i" );
				var rgm = v.match(rg);
				if( rgm ){
					v = v.replace(rgm, "zzzz"+rgm+"-zzzz");
				}
			}
			v = v.replace( /\-zzzz/g, "</span>" );
			v = v.replace( /zzzz/g, "<span class='text-danger'>" );
			return v;
		},
		gen: function(){
			var vlist = [];
			var vlist_ = {};
			var vkey = this.keyword+'';
			var w = vkey.split(/\W+/g);
			w.reverse();
			var key2 = w.join(".*");
			var k = vkey.trim().replace(/\W+/g, ".*");
			var kpr = new RegExp("^"+k,"i");
			for(var i=0;i<this.keywords.length;i++){
				if( this.keywords[i].match(kpr) ){
					vlist.push(this.keywords[i]);
					vlist_[ i ] = 1;
					if( vlist.length > 100 ){
						break;
					}
				}
			}
			if( vlist.length < 100 ){
				var kpr = new RegExp(k,"i");
				var kpr2 = new RegExp(key2,"i");
				for(var i=0;i<this.keywords.length;i++){if( i in vlist_ == false ){
					if( this.keywords[i].match(kpr) || this.keywords[i].match(kpr2) ){
						vlist.push(this.keywords[i]);
						if( vlist.length > 200 ){
							break;
						}
					}
				}}
			}
			this.suggestions = vlist;
		},
		keyup: function(e){
			if( e.keyCode == 27 || e.keyCode == 13 || e.keyCode == 9 ){
				this.vfocused = false;
				return 0;
			}
			setTimeout(this.gen,200);
			var k = this.keyword.trim().replace(/\W+/g, ".*");
			var kpr = new RegExp(k,"i");
			if( this.keyword in this.keys == false ){
				this.$set( this.keys, this.keyword, {
					"kpr": kpr,
					"l": 0,
					"keys": [],
					"ready": false,
					"done": false,
				});
				if( this.keyword.length < 3 ){
					this.$set( this.keys, 'done', true);
					this.$set( this.keys, 'ready', true);
					this.$set( this.keys, 'l', this.limit);
				}
			}
			var t = new Date().getTime();
			if( this.keyword.length > 2 ){
				if( this.keyword.length > 1 ){
					var k_1 = this.keyword.substr(0,this.keyword.length-1);
					if( k_1 in this.keys ){
						if( Number(this.keys[ k_1 ]['l']) < Number(this.limit) && this.keys[ k_1 ]['ready'] ){
							this.$set( this.keys[ this.keyword ], 'l', Number(this.keys[ k_1 ]['l']) );
							this.$set( this.keys[ this.keyword ], 'ready', true );
							return false;
						}
					}
				}
				if( this.keys[ this.keyword ]['ready'] == false && this.isbusy == false ){
					this.isbusy = true;
					this.docall( this.keyword );
				}else{

				}
			}
		},
		docall: function(vk){
			if( vk.length > 2 ){
			var vu = this.api_url+'';
			vu = vu.replace("##KEYWORD##", encodeURIComponent(vk) );
			vu = vu.replace("##LIMITW#", this.limit );
			axios.get(vu).then(response=>{
				if( response.status == 200 ){
				if( typeof(response.data) == "object" ){
				if( "status" in response.data ){
				if( response.data['status'] == 'success' ){
					this.isbusy = false;
					var r = response.data['keywords'];
					var vkeywords = JSON.parse(JSON.stringify(this.keywords));
					for(var i=0;i<r.length;i++){
						if( r[i]['_id'] in this.keywords_ == false ){
							var l = r[i]['label'];
							if( r[i]['m'] == "n" ){
								l = r[i]['_id2'] + " (" + r[i]['label'] + ")";
							}
							console.log( l );
							vkeywords.push( l+"#"+r[i]['id'] );
							this.$set( this.keywords_, r[i]['_id'], true );
						}
					}
					vkeywords.sort(function(a, b){return a - b});
					this.keywords = vkeywords;
					this.$set( this.keys[ response.data['keyword'] ], 'ready', true );
					this.$set( this.keys[ response.data['keyword'] ], 'l', r.length );
					this.$set( this.keys[ response.data['keyword'] ], 'done', true );
					setTimeout(this.gen(), 100);

					for( vk in this.keys ){ if( this.keys[ vk ]['ready'] == false ){ this.docall( vk ); } }

					// if( this.keyword == response.data['keyword'] ){ this.key = this.keyword; }else
					// if( this.keyword == response.data['keyword'] ){ this.key = this.keyword; }
				}else{
					console.error("Search app v1, response error: " + response.data['error']);
				}
				}else{
					console.error("Search app v1, response staus missing");
				}
				}else{
					console.error("Search app v1, Incorrect response: ". typeof(response.data));
				}
				}else{
					console.error("Search app v1, Incorrect response: " + response.status);
				}
			});
			}
		}
	},
	template: `<div style="position: relative; " >
		<div v-if="vshow" style="position: absolute; border: 1px solid #999; height:300px; padding: 5px; width:95%; resize:both; overflow: auto; background-color: white; box-shadow:2px 2px 4px #aaa; z-index:500;" >
			<div class="btn btn-default btn-sm float-right" v-on:click="vshow=false" >&#9747;</div>
			<input type="text" class="form-control form-control-sm" style="width:calc( 100% - 30px )" v-model="keyword" v-on:keyup="keyup" ref="input" >
			<div v-if="suggestions.length>0" style=" padding: 5px; height: calc( 100% - 90px ); overflow: auto; background-color: white;">
				<div style="border-bottom:1px solid #eee;white-space: nowrap;cursor:pointer;"  v-for="v,i in suggestions" v-bind:data-id="getid(v)" v-html="vformat(v)"  v-on:click="selecte(v)" ></div>
			</div>
		</div>
		<div style="border:1px solid #999; padding:2px; line-height:20px;cursor:pointer;" v-on:click="showit" >{{ display_text }}</div>
	</div>`
	});
	return new_app;
}
