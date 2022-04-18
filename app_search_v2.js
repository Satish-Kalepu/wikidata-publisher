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
document.write(`<style>
.icon-container {
  position: absolute;
  right: 10px;
  top: 5px;
}
.loader {
  position: relative;
  height: 20px;
  width: 20px;
  display: inline-block;
  animation: around 5.4s infinite;
}

@keyframes around {
  0% {
    transform: rotate(0deg)
  }
  100% {
    transform: rotate(360deg)
  }
}

.loader::after, .loader::before {
  content: "";
  background: white;
  position: absolute;
  display: inline-block;
  width: 100%;
  height: 100%;
  border-width: 2px;
  border-color: #333 #333 transparent transparent;
  border-style: solid;
  border-radius: 20px;
  box-sizing: border-box;
  top: 0;
  left: 0;
  animation: around 0.7s ease-in-out infinite;
}

.loader::after {
  animation: around 0.7s ease-in-out 0.1s infinite;
  background: transparent;
}
</style>`);

function create_search_app_v2( vops ){

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
			this.display_text = i['label'];
			if( document.getElementById(this.dest_hidden_input_id) == undefined ){
				console.error( "Element: "+this.dest_hidden_input_id + " not found" );
			}else{
				document.getElementById( this.dest_hidden_input_id ).value = i['label'];
			}
			eval( this.select_event + "('"+ i['id'] +"')" );
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
			var v = vv['_id2']+'';
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
			if( 'label_in' in vv ){
				if( vv['m'] == 'n' ){
					v = v + " ("+ vv['label'] +") in " + vv['label_in'];
				}else{
					v = v + " in " + vv['label_in'];
				}
			}
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
				if( this.keywords[i]['_id2'].match(kpr) ){
					vlist.push(this.keywords[i]);
					vlist_[ this.keywords[i]['label'] ] = 1;
					if( vlist.length > 200 ){
						break;
					}
				}
			}
			if( vlist.length < 100 ){
				var kpr = new RegExp(k,"i");
				var kpr2 = new RegExp(key2,"i");
				for(var i=0;i<this.keywords.length;i++){if( this.keywords[i]['label'] in vlist_ == false ){
					if( this.keywords[i]['_id2'].match(kpr) || this.keywords[i]['_id2'].match(kpr2) ){
						vlist.push(this.keywords[i]);
						if( vlist.length > 300 ){
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
					if( this.keyword.length > 2){
						this.docall( this.keyword );
					}
				}else{

				}
			}
		},
		docall: function(vk){
			if( vk.length > 2 ){
			console.log( "docall: " + vk );
			var vu = this.api_url+'';
			vu = vu.replace("##KEYWORD##", encodeURIComponent(vk) );
			vu = vu.replace("##LIMITW#", this.limit );
			axios.get(vu).then(response=>{
				if( response.status == 200 ){
				if( typeof(response.data) == "object" ){
				if( "status" in response.data ){
				if( response.data['status'] == 'success' ){
					var r = response.data['keywords'];
					for(var i=0;i<r.length;i++){
						if( r[i]['_id'] in this.keywords_ == false ){
							this.keywords.push( r[i] );
							this.$set( this.keywords_, r[i]['_id'], true );
						}
					}

					while( 1 ){
						var f = false;
						for(var i=0;i<this.keywords.length-1;i++){
							if( this.keywords[ i ]['_id2'] > this.keywords[ i+1 ]['_id2'] ){
								var t = this.keywords[i];
								this.keywords[i] = JSON.parse(JSON.stringify(this.keywords[i+1]));
								this.keywords[i+1] = t;
								f = true;
							}
						}
						if( f == false ){break;}
					}

					this.$set( this.keys[ response.data['keyword'] ], 'ready', true );
					this.$set( this.keys[ response.data['keyword'] ], 'l', r.length );
					this.$set( this.keys[ response.data['keyword'] ], 'done', true );
					setTimeout(this.gen(), 100);

					var k_1 = response.data['keyword']+'';
					if( Number(this.keys[ k_1 ]['l']) < Number(this.limit) ){
						for( k in this.keys ){
							if( k.length > k_1.length && this.keys[ k ]['ready'] == false ){
								this.$set( this.keys[ k+'' ], 'l', Number(r.length) );
								this.$set( this.keys[ k+'' ], 'ready', true );
							}
						}
					}

					for( vk in this.keys ){
						if( this.keys[ vk ]['ready'] == false ){if( vk.length > 2 ){
							this.isbusy = true;setTimeout(this.docall,100,vk+'');
							return 0;
						}}
					}
					this.isbusy = false;
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
				<div v-if="isbusy" class="icon-container"><i class="loader"></i></div>
			<div v-if="suggestions.length>0" style=" padding: 5px; height: calc( 100% - 90px ); overflow: auto; background-color: white;">
				<div style="border-bottom:1px solid #eee;white-space: nowrap;cursor:pointer;"  v-for="v,i in suggestions" v-html="vformat(v)" v-on:click="selecte(v)" ></div>
			</div>
		</div>
		<div style="border:1px solid #999; padding:2px; line-height:20px;cursor:pointer;" v-on:click="showit" >{{ display_text }}</div>
	</div>`
	});
	return new_app;
}
