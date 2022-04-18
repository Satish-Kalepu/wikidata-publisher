
db.wiki_data.find({},{i_of:1}).limit(5).sort({_id:1})

db.wiki_data.find({}).sort({_id:1}).forEach(function(i){
	var d = [];
	if( 'claims' in i ){
	if( 'P31' in i['claims'] ){
		for(var j=0;j<i['claims']['P31'].length;j++){
			d.push( i['claims']['P31'][j]['v'] );
		}
	}
	}
	db.wiki_data.updateOne({_id:i._id},{$set:{i_of:d}});
})

var key = -1;var cnt = 0;
db.wiki_data.find({wiki_id:{$gt:0}},{wiki_id:1}).sort({wiki_id:1}).limit(5000).forEach(function(i){
	if( i.wiki_id != key ){
		key = i.wiki_id;
		cnt = 1;
	}else{
		cnt = cnt+1;
		db.wiki_data.deleteOne({_id:i._id});
		print('deleted');
	}
	print(i.wiki_id+ ": " + cnt)
})

var key = -1;var cnt = 0;var dels = 0;
db.wiki_data.find({wiki_id:{$gt:0}},{wiki_id:1}).sort({wiki_id:1}).forEach(function(i){
	cnt=cnt+1
	if( i.wiki_id != key ){
		key = i.wiki_id;
		cnt = 1;
	}else{
		dels = dels+1;
		print(cnt+": " + dels);
		db.wiki_data.deleteOne({_id:i._id});
	}
})

db.wiki_data.find({}).limit(100).
forEach( function(i) {
  db.sample_data.insert(i);
});

db.wiki_data.find({},{"claims.P31.v":1}).limit(5).forEach(function(i){
	print(JSON.stringify(i));
	for( var v in i.claims.P31 )
})

db.wiki_data.aggregate([
	{$unwind: "$claims.P31"},
	{$project: {_id:1,label:1,"claims.P31.v":1}},
	{$limit: 1000}
])

db.wiki_data.aggregate([
	{$project: {_id:1,"claims.P31.v":1}},
	{$unwind: "$claims.P31"},
	{$limit: 1000},
	{$group: { _id: {"v":"$claims.P31.v","l":"$l"}, cnt: {$sum:1} }},
],{allowDiskUse:true})

db.wiki_data.aggregate([
	{$project: {_id:1,"claims.P31.v":1}},
	{$unwind: "$claims.P31"},
	{$group: { _id: {"v":"$claims.P31.v","l":"$l"}, cnt: {$sum:1} }},
],{allowDiskUse:true}).forEach(function(e){
	db.index_stats.insert({_id:e.v,cnt:cnt});
})

db.wiki_instances.find({}).sort({_id:1}).forEach(function(a){
	var v = db.wiki_data.findOne({id:a._id});
	if(v){
		db.wiki_instances.updateOne({_id:a._id},{$set:{des:v.des} });
	}
})

var props = {};
db.wiki_data.find({}).forEach(function(a){
	for( var p in a.claims ){
		if( p in props == false ){
			props[ p ] = 1;
		}else{
			props[ p ] += 1;
		}
	}
});
for( p in props ){
	db.wiki_props.updateOne({id:p}, {$set:{cnt:props[p]}});
}

db.wiki_data.aggregate([
	{$unwind: "$claims.P31" },
	{$group: {'_id': "$claims.P31.v", 'cnt': {$sum:1} } }
]).forEach(function(i){
	db.wiki_instances.insert(i);
});
db.sample_data.aggregate([
	{$project: {claims: {$objectToArray:"$claims"}} },
	{$unwind: "$claims" },
	{$limit: 5}
])
db.sample_data.aggregate([
	{$project: {claims: $objectToArray:"$claims"}},
	{$unwind: "$claims" },
	{$group: {'_id': "$claims.$key", 'cnt': {$sum:1} } }
]).forEach(function(i){
	print(i);
	db.wiki_props.updateOne({id:i._id},{cnt:i.cnt});
});

db.sample_data.aggregate([
	{$unwind: "$claims.P31" },
	{$limit: 100},
	{$group: {'_id': "$claims.P31.v", 'cnt': {$sum:1} } }
])
	{$group: {'_id': "$claims.P31.v", 'cnt': {$sum:1} } },
	{$limit: 100}


var instances = {};
db.wiki_instances.find({}).forEach( function(i){ instances[ i._id ] = i.cnt; } )

db.wiki_data.find({}).forEach(function(i){

	if( instances[ i.id ].cnt > 1000 )

	db[ i.id ].insert()

});

db.wiki_data.find({}).sort({wiki_id:1}).limit(1000).forEach( function(d){
	for(var i=0;i<d['i_of'].length;i++){
		print(d['id']+":"+d['i_of'][i]+":"+d['wiki_id'] +": " + d['id']);
		d[ 'inst' ] = d['i_of'][i];
		d['_id'] = d['i_of'][i] + ":" + (100000000+d['wiki_id']);
		db.wiki_data2.insert( d );
	}
});
db.wiki_data.find({}).limit(1000).forEach( function(d){
	for(var i=0;i<d['i_of'].length;i++){
		print(d['id'] + ": " + d['i_of'][i]);
	}
});

db.wiki_data.find({}).sort({_id:1}).forEach( function(d){
	for(var i=0;i<d['i_of'].length;i++){
		d['id_id'] = d['_id'];
		d['_id'] = d['i_of'][i] + ":" + d['label'];
		db.wiki_data3.insert( d );
	}
});


var delete_entries = {"Q16521": 1,
"Q67206691": 1,
"Q523": 1,
"Q4167836": 1,
"Q47150325": 1,
"Q4167410": 1,
"Q79007": 1,
"Q101352": 1,
"Q11266439": 1,
"Q486972": 1,
"Q9842": 1,
"Q54050": 1,
"Q21014462": 1,
"Q3863": 1,
"Q67383935": 1,
"Q19855165": 1,
"Q16970": 1,
"Q29654788": 1,
"Q29654788": 1,
"Q2154519": 1,
"Q19389637": 1,
"Q7187": 1,
"Q22969563": 1,
"Q2225692": 1,
"Q49008": 1,
"Q1153690": 1,
"Q59199015": 1,
"Q318": 1,
"Q3947": 1,
"Q23397": 1,
"Q13442814": 1,
"Q5633421": 1,
"Q47521": 1,
"Q3331189": 1,
"Q726242": 1,
"Q23038290": 1,
"Q67383935": 1,
"Q19855165": 1,
"Q27555384": 1,
"Q204107": 1,
"Q7604686": 1,
"Q427087": 1,
"Q726242": 1,
"Q67206701": 1,
"Q1260524": 1,
"Q6243": 1,
"Q1348305": 1,
"Q71963409": 1,
"Q1151284": 1,
"Q22698": 1,
"Q2247863": 1,
"Q66619666": 1,
"Q72802727": 1,
"Q22808320": 1,
"Q67206785": 1,
"Q23058136": 1,
"Q277338": 1,
"Q26211545": 1,
"Q2168098": 1,
"Q55659167": 1};

var cnt = 0;
db.wiki_data.find({}).sort({_id:1}).forEach(function(d){
	for(var i=0;i<d['i_of'].length;i++){
		if( d['i_of'][i] in delete_entries ){
			db.wiki_data.deleteOne({_id: d._id});
			cnt++;
			print(cnt);
			break;
		}
	}
});


var cnt = 0;
db.wiki_data3.find({}).sort({_id:1}).forEach(function(d){
	for(var i=0;i<d['i_of'].length;i++){
		if( d['i_of'][i] in delete_entries ){
			db.wiki_data3.deleteOne({_id: d._id});
			cnt++;
			print(cnt);
			break;
		}
	}
});


for(var id in delete_entries){
	print("Deleting: " + id);
	var k = {_id:{$gt:id+":",$lt:id+":zzzzzz"} };
	var rs = db.keywords.deleteMany(k);
	print(JSON.stringify(rs));
}
