<template>
	<view class="wrap">
		<view class="write_title">
			<input type="text" v-model="title" placeholder="请输入标题" />
		</view>
		<!-- 内容展示区 -->
		<view class="artList">
			<block v-for="(item, index) in artList" :key="index">
				<view class="item" v-if="item.type == 'image'"><image :src="item.content" :data-index="index" mode="widthFix" @tap="removeImg" /></view>
				<view class="item" v-if="item.type == 'text'">
					<textarea auto-height="ture" v-model="artList[index].content" ></textarea>
					<view :data-index="index" class="deleteText" @tap="deleteText">删除</view>
				</view>
			</block>
		</view>
		<!-- 输入区 -->
		<form @submit="submit">
			<view class="inputArea">
				<view class="addText">
					<textarea name="artText" maxlength="-1" v-model="inputContent" placeholder="请输入文本" ></textarea>
				</view>
				<view class="addImg" @tap="addImg"></view>
				<button type="primary" form-type="submit">添加</button>
			</view>
		</form>
		<!-- 选择分类 -->
		<view class="art-cate">
			<view class="cate-title">帖子分类</view>
			<view class="cate-chose">
				<picker mode="selector" :range="caties" @change="cateChange">
					<view>{{caties[currentCateIndex]}}</view>
				</picker>
			</view>
		</view>
		<!-- 提交按钮 -->
		<view class="submitNow" v-if="artList.length > 0" @tap="submitNow">发布文章</view>
	</view>
</template>
<script>
var _self, loginRes;
var signModel = require('../../commons/sign.js');
export default {
	data() {
		return {
			title : '',
			artList : [],
			inputContent : "",
			needUploadImg : [],
			uploadIndex : 0,
			//分类
			caties : ['点击选择'],
			currentCateIndex : 0,
			catiesFromApi : [],
			// 记录真实选择的api接口数据的分类id
			sedCateIndex  : 0
		};
	},
	onLoad : function() {
		_self = this;
		loginRes = this.checkLogin('../home/home', '2');
		if(!loginRes){return false;}
		// 签名
		signModel.sign(_self.apiServer);
		// 加载文章分类
		uni.request({
			url: _self.apiServer+'category&m=index',
			method: 'GET',
			success: res => {
				console.log(res);
				if(res.data.status == 'ok'){
					// 把数据格式整理为 picker 支持的格式 ['分类名', ...]
					var categories = res.data.data;
					for(var k in categories){
						_self.caties.push(categories[k]);
					}
					// 记录分类信息
					_self.catiesFromApi = categories;
				}
			}
		});
	},
	onShow:function(){
		loginRes = this.checkLogin('../home/home', '2');
		if(!loginRes){return false;}
		// 重新请签名
		signModel.sign(_self.apiServer);
	},
	methods:{
		cateChange : function(e){
			var sedIndex          = e.detail.value;
			this.currentCateIndex = sedIndex;
			// 获取选择的分类名称
			if(sedIndex < 1){return ;}
			var cateName = this.caties[sedIndex];
			for(var k in this.catiesFromApi){
				if(cateName == this.catiesFromApi[k]){
					this.sedCateIndex = k;
					break;
				}
			}
			this.currentCateIndex = sedIndex;
		},
		removeImg : function(e){
			var index = e.currentTarget.dataset.index;
			uni.showModal({
				content:"确定要删除此图片吗",
				title:'提示',
				success(e) {
					if (e.confirm) {
						_self.artList.splice(index, 1);
					}
				}
			});
		},
		deleteText : function(e){
			var index = e.currentTarget.dataset.index;
			uni.showModal({
				content:"确定要删除吗",
				title:'提示',
				success(e) {
					if (e.confirm) {
						_self.artList.splice(index, 1);
					}
				}
			});
		},
		submitNow : function(){
			// 数据验证
			if(this.title.length < 2){uni.showToast({title:'请输入标题', icon:"none"}); return ;}
			if(this.artList.length < 1){uni.showToast({title:'请填写文章内容', icon:"none"}); return ;}
			if(this.sedCateIndex < 1){uni.showToast({title:'请选择分类', icon:"none"}); return ;}
			// 上传图片 一次一个多次上传 [ 递归函数 ]
			// 上传完成后整体提交数据
			// 首先整理一下需要上传的图片
			// this.needUploadImg = [{tmpurl : 临时地址, index : 数据索引}]
			this.needUploadImg = [];
			for(var i = 0; i < this.artList.length; i++){
				if(this.artList[i].type == 'image'){
					this.needUploadImg.push({"tmpurl" : this.artList[i].content , "indexID" : i});
				}
			}
			console.log(this.needUploadImg);
			this.uploadImg();
		},
		uploadImg : function(){
			// 如果没有图片 或者已经上传完成 则执行提交
			if(this.needUploadImg.length < 1 || this.uploadIndex >=  this.needUploadImg.length){
				//
				uni.showLoading({title:"正在提交"});
				var sign = uni.getStorageSync('sign');
					uni.request({
						url: this.apiServer + 'posts&m=add',
						method: 'POST',
						header: {'content-type' : "application/x-www-form-urlencoded"},
						data:{
							title   : _self.title,
							content : JSON.stringify(_self.artList),
							uid     : loginRes[0],
							random  : loginRes[1],
							cate    : _self.sedCateIndex,
							sign    : sign
						},
						success:function(res){
							console.log(res);
							if(res.data.status == 'ok'){
								uni.showToast({title:"提交成功", icon:"none"});
								_self.artList = [];
								// 清空数据
								/* signModel.sign(_self.apiServer); */
								// 防止数据缓存
								_self.currentCateIndex = 0;
								_self.sedCateIndex     = 0;
								_self.needUploadImg    = [];
								_self.title            = '';
								setTimeout(function(){
									uni.switchTab({
										url:'../home/home'
									})
								}, 1000);
							}else{
								uni.showToast({title:res.data.data, icon:"none"});
							}
						}
					});
				
			}else{
				//上传图片
				uni.showLoading({title:"上传图片"});
				var uploader = uni.uploadFile({
					url      : _self.apiServer + 'uploadimg',
					filePath : _self.needUploadImg[_self.uploadIndex].tmpurl,
					header: { 'content-type': "application/x-www-form-urlencoded" },
					name     : 'file',
					success: (uploadFileRes) => {
						console.log(uploadFileRes);
						uploadFileRes = JSON.parse(uploadFileRes.data);
						if(uploadFileRes.status != 'ok'){
							uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
							return false;
						}
						// 将已经上传的文件地址赋值给文章数据
						var index = _self.needUploadImg[_self.uploadIndex].indexID;
						_self.artList[index].content = _self.staticServer + uploadFileRes.data;
						_self.uploadIndex ++;
						// 递归上传
						setTimeout(function(){_self.uploadImg();}, 1000);
					},
					fail: (e) => {
						console.log(e);
						uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
					}
				})
			}
		},
		submit : function(res){
			var content = res.detail.value.artText;
			if(content.length < 1){uni.showToast({title:"请输入内容",icon:'none'}); return ;}
			this.artList.push({"type":"text", "content" : content});
			this.inputContent = '';
		},
		addImg : function(){
			uni.chooseImage({
				count: 1,
				sizeType: ['compressed'],
				success: function(res) {
					_self.artList.push({"type":"image", "content" : res.tempFilePaths[0]})
				}
			})
		}
	}
}
</script>
<style lang="scss">
	.wrap{
			textarea{
				width: 100%;
				word-wrap: break-word;
			}
			.artList{
				.deleteText{
					border-top-style:solid;
					border-width:1rpx;
					border-color: #a1a2a567;
					text-align: center;
					color: red;
				}
			}
		.write_title{
			display: flex;
			flex-direction: row;
			padding: 15rpx 0rpx;
			font-weight: 200;
			font-size: 35rpx;
			border-bottom-style:solid;
			border-width:1rpx;
			border-color: #F0F8FF;
			align-items: center;
			justify-content: center;
			input{
				width: 90%;
				height: 75rpx;
				font-weight: 200;
				font-size: 30rpx;
				text-align: center;
				border: 4rpx solid #6699CC;
				border-radius: 20rpx;
			}
		}
		.inputArea{
			  width: 100%;
			  display: flex;
			  flex-direction: column;
			  margin-top: 5rpx;
			  background: white;
			.addText{
				border: 4rpx solid #6699CC;
				border-radius: 20rpx;
				margin: 15rpx 15rpx;
			}
			button{
				color: black;
				height: 90rpx;
				width: 90%;
				background: linear-gradient(110deg,#6699CC 0%, #5699CC 100%);
				border-radius: 20rpx;
				box-shadow: 0px 0px 8rpx 2rpx rgba(30, 117, 247, 0.507); 
				display: flex;
				flex-direction: column;
				align-content: center;
				justify-content: center;
			}
			.addImg{
				margin: 15rpx 15rpx;
				width: 20%;
				height: 125rpx;
				font-weight: 200;
				font-size: 30rpx;
				line-height: 125rpx;
				text-align: center;
				border: 4rpx dashed #6699CC;
				border-radius: 20rpx;
				background-image: url('../../static/icon/select-image.png');
				background-repeat: no-repeat;
				background-size: 100% 100%;
			}
		}
		.art-cate{
			margin: 0 auto;
			margin-top: 15rpx;
			margin-bottom: 15rpx;
			height: 50rpx;
			width: 90%;
			background: linear-gradient(110deg,#6699CC 0%, #5699CC 100%);
			border-radius: 20rpx;
			box-shadow: 0px 0px 8rpx 2rpx rgba(30, 117, 247, 0.507); 
			display: flex;
			flex-direction: row;
			align-content: center;
			justify-content: space-between;
			text-align: center;
			line-height: 50rpx;
			.cate-title{
				margin-left: 30rpx;
			}
			.cate-chose{
				height: 30rpx;
				line-height: 30rpx;
				margin-right: 30rpx;
				margin-top: 10rpx;
				background: linear-gradient(110deg,#1638f7b4 0%, #1638f767 100%);
				border-radius: 20rpx;
				box-shadow: 0px 0px 8rpx 2rpx rgba(30, 117, 247, 0.507); 
			}
		}
		.submitNow{
			margin: 0 auto;
			margin-top: 15rpx;
			margin-bottom: 15rpx;
			height: 90rpx;
			width: 90%;
			background: linear-gradient(110deg,#6699CC 0%, #5699CC 100%);
			border-radius: 20rpx;
			box-shadow: 0px 0px 8rpx 2rpx rgba(30, 117, 247, 0.507); 
			display: flex;
			flex-direction: column;
			align-content: center;
			justify-content: center;
			text-align: center;
			line-height: 90rpx;
			color: black;
		}
	}
</style>