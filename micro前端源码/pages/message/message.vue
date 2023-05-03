<template>
	<view class="letter-container">
	
	  <scroll-view scroll-y 
	              :scroll-top="scrollTop" 
	              v-for="(item, index) in list" 
				  :key="id"
	              class='letter-content'>
	  <block v-if="item.random_r == Random">
	    <view class="scroll-view-item">
	      <view class='user-left'>
	
	        <view class='left-content-avatar'>
	          <image class="avatar-border" src="../../static/icon/border.png"></image>
	          <image class="avatar-item" :src='item.face'></image>
	        </view>
	
	        <view class='left-content'>
	          <view class='left-content-message'>
	          <view class="qipao"></view>
	            <view v-if="item.message != null" 
	                  class='left-content-item'>
	                  {{item.message}}
	            </view>
	            <view class='left-content-item' 
	                  v-if="item.img != null">
	              <image mode='widthFix' 
	                     @tap='showImgs'
						 :data-url="item.img"
	                     :src="item.img"></image>
	            </view>
	          </view>
	          <view class='left-time'>{{item.createtime}}</view>
	        </view>
	
	      </view>
	          
	    </view>
	  </block>
	
	  <block v-if="item.random_s == Random">
	    <view class="scroll-view-item">
	      <view class='user-right'>
	
	        <view class='right-content' 
	              :data-objid="item.id"
	              @longpress="deleteContent">
	          <view class='right-content-message'>
	            <view class='right-content-item'
	                  v-if="item.message != null">
	                  {{item.message}}
	            </view>
	            <view class='right-content-item' 
	                  v-if="item.img != null">
	              <image mode='widthFix' 
	                     @tap='showImgs'
						 :data-url="item.img"
	                     :src="item.img"></image>
	            </view>
	            <view class="qipao-right"></view>
	          </view>
	          <view class='right-time'>{{item.createtime}}</view>
	        </view>
	
	        <view class='right-content-avatar'>
	          <image class="avatar-border" src="../../static/icon/border.png"></image>
	          <image  class="avatar-item" :src='item.face'></image>
	        </view>
	
	      </view>
	          
	    </view>
	  </block>
	
	
	  </scroll-view>
	
	  <view class='hei' id="hei"></view>  
	
	  <view class='comment-input-container'>
	    <view class='add-image' @tap='sendImage'></view>
	    <textarea class='text-content'
	              maxlength="1024" 
	              v-model="content" 
	              fixed="true"
	              placeholder="请输入内容"
	              cursor-spacing="10"
	              auto-height/>
	    <view class='send-button' @tap='send'>
	      <view>发送</view>
	    </view>
	  </view>
	
	</view>
</template>

<script>
	var _self,follow_random,error,page,username;
	export default {
		data() {
			return {
				Random:'',
				content:'',
				tmpimg:'',
				img:'',
				list:[],
			}
		},
		onLoad(option) {
			_self = this;
			follow_random = option.random;
			username = option.username;
			uni.setNavigationBarTitle({
				title:username
			});
			_self.Random = uni.getStorageSync('SRAND');
		},
		onShow() {
			page = 1;
			_self.list = [];
			_self.getMessageList();
			setTimeout(()=>{
				uni.pageScrollTo({
					scrollTop:9999999999,
					duration:200,
					success() {
						console.log(1);
					}
				})
			},1000)
		},
		onPullDownRefresh : function(){
		    _self.getMessageList();
		},
		methods: {
			getMessageList:function(){
				uni.request({
				    url: this.apiServer + 'message&m=getMessageList&random='+_self.Random+'&follow_random='+follow_random+'&page='+page,
				    method: 'GET',
				    success: res => {
						console.log(res);
				        if(res.data.status == 'ok'){
				            var newlist = res.data.data;
							_self.list = newlist.concat(_self.list);
							console.log(_self.list);
							page++;
				        }
						else{
							uni.showToast({
							    title: '还没有任何消息',
							    icon: 'none',
							    duration: 2000
							});
						}
				    },
					complete:function(){
					    uni.stopPullDownRefresh();
						}
				});
			},
			showImgs : function(e){
				var currentUrl = e.currentTarget.dataset.url;
				var imgsNeedShow = [];
				for(var i = 0; i < this.list.length; i++){
					if(this.list[i].img != null){
						imgsNeedShow.push(this.list[i].img);
					}
				}
				uni.previewImage({
					urls    :imgsNeedShow,
					current :currentUrl
				});
			},
			send:function(){
				if(_self.content.length < 1){uni.showToast({title:"请输入内容",icon:'none'}); return ;}
				uni.request({
					url: this.apiServer + 'message&m=add',
					method: 'POST',
					header: {'content-type' : "application/x-www-form-urlencoded"},
					data:{
						message : _self.content,
						random_s  :_self.Random,
						random_r  :follow_random
					},
					success:function(res){
						console.log(res);
						if(res.data.status == 'ok'){
							_self.content = '';
							_self.list = [];
							page = 1;
							_self.getMessageList();
						}else{
							if(res.data.status == 'error1'){
								uni.showToast({
								    title: '请先登录',
								    icon: 'none',
								    duration: 2000
								});
							}else{
								uni.showToast({
								    title: '发送失败!',
								    icon: 'none',
								    duration: 2000
								});
							}
						}
					}
				});
			},
			sendImage:function(){
				uni.chooseImage({
					count: 1,
					sizeType: ['compressed'],
					success: function(res) {
						_self.tmpimg = res.tempFilePaths[0];
						uni.uploadFile({
							url      : _self.apiServer + 'uploadimg',
							filePath : _self.tmpimg,
							header: { 'content-type': "application/x-www-form-urlencoded" },
							name     : 'file',
							success: (uploadFileRes) => {
								console.log(uploadFileRes);
								uploadFileRes = JSON.parse(uploadFileRes.data);
								if(uploadFileRes.status != 'ok'){
									uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
									return false;
								}
								_self.img = _self.staticServer + uploadFileRes.data;
								uni.request({
									url: _self.apiServer + 'message&m=addimg',
									method: 'POST',
									header: {'content-type' : "application/x-www-form-urlencoded"},
									data:{
										img : _self.img,
										random_s  :_self.Random,
										random_r  :follow_random
									},
									success:function(res){
										console.log(res);
										if(res.data.status == 'ok'){
											_self.content = '';
											_self.list = [];
											page = 1;
											_self.getMessageList();
										}else{
											uni.showToast({
											    title: '发送失败!',
											    icon: 'none',
											    duration: 2000
											});
										}
									}
								});
							},
							fail: (e) => {
								console.log(e);
								uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
							}
						})
					}
				})
			}
		}
	}
</script>

<style>
.letter-container{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-top-style:solid;
  border-width:1rpx;
  border-color: #F0F8FF;
}

.letter-content{
  width: 95%;
  display: flex;
  flex-direction: column;
  margin-bottom: 50rpx;
}

.scroll-view-item{
  display: flex;
  flex-direction: row;
  width: 100%;
  margin-top: 20rpx;
  border-bottom-style:solid;
  border-width:1rpx;
  border-color: gainsboro;
  padding-bottom: 20rpx;
}

.scroll-view-item .user-left{
  width: 100%;
  display: flex;
  flex-direction: row;
}

.user-left .left-content-avatar{
  width: 17%;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.left-content-avatar .avatar-item{
  width: 90rpx;
  height: 90rpx;
  border: 4rpx solid #303030;
  border-radius: 30rpx;
  margin-left: 10rpx;
}

.left-content-avatar .avatar-border{
  width: 70rpx;
  height: 70rpx;
  position:absolute;
  z-index: 100;
  left: -7rpx;
  top: 4rpx;
}

.left-content{
  display: flex;
  flex-direction: column;
  width: 70%;
}

.left-content .left-content-message{
  display: flex;
  flex-direction: row;
}

.left-content-message .left-content-item{
  border-radius: 20rpx;
  padding: 20rpx;
  background: white;
  border: 2rpx solid #303030;
  margin-left: 28rpx;
}

.left-content image{
  width: 350rpx;
}

.right-content image{
  width: 350rpx;
}

.left-content .left-time{
  margin-top: 15rpx;
  margin-left: 30rpx;
  font-size: 20rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #303030;
  line-height: 26fpx;
}

.left-content-message .qipao{
  width: 15rpx;
  height: 15rpx;
  background: white;
  margin-left: 20rpx;
  margin-top: 20rpx;
  border-color: #303030;
  position: absolute;
  z-index: 100;
  border-bottom-style:solid;
  border-right-style:solid;
  border-width:2rpx;
  transform: rotate(140deg);
}

.qipao-right{
  width: 15rpx;
  height: 15rpx;
  background: #ffffff;
  margin-top: 20rpx;
  border-color: #303030;
  position: absolute;
  z-index: 100;
  border-top-style:solid;
  border-left-style:solid;
  border-width:2rpx;
  transform: rotate(135deg);
  margin-right: -10rpx;
}

/** right **/
.scroll-view-item .user-right{
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
}

.user-right .right-content-avatar{
  width: 17%;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-end;
  margin-right: 10rpx;
}

.right-content-avatar .avatar-item{
  width: 90rpx;
  height: 90rpx;
  border: 4rpx solid #303030;
  border-radius: 30rpx;
}

.right-content-avatar .avatar-border{
  width: 70rpx;
  height: 70rpx;
  position:absolute;
  z-index: 100;
  right: 55rpx;
  top: 4rpx;
}

.right-content{
  display: flex;
  flex-direction: column;
  width: 70%;
  justify-content: flex-end;
  margin-right: 25rpx;
}

.right-content .right-content-message{
  display: flex;
  flex-direction: row;
  justify-content: flex-end;

}

.right-content-message .right-content-item{
  background: #ffffff;
  padding: 20rpx;
  border-radius: 20rpx;
  border: 2rpx solid #303030;
  margin-right: -5rpx;
}

.right-content .right-time{
  margin-top: 15rpx;
  display: flex;
  justify-content: flex-end;
  font-size: 20rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #303030;
  line-height: 26fpx;
}


/** 底部发送 **/
.comment-input-container{
  position:fixed;
  bottom: 0rpx;
  z-index:100;
  width: 100%;
  height: 250prx;
  display: flex;
  flex-direction: row;
  align-items: center;
  padding-top: 10rpx;
  background: #ffffff;
  box-shadow: 0px 0px 10rpx 0px rgba(0,0,0,0.34); 
}

.comment-input-container input{
  width: 100%;
  margin-left: 20rpx;
  border-bottom-style:solid;
  border-width:1rpx;
  border-color: #0aecc3;
  margin-right: 20rpx;
  padding-bottom: 15rpx;
  font: 30rpx;
  font-weight: 200;
  margin-bottom: 30rpx;
  padding-top: 17rpx;
}

.send-button{
  width: 15%;
  font-size: 28rpx;
  margin-right: 20rpx;
  display: flex;
  flex-direction: row;
  justify-content: space-around;
  align-items: center;
  border-radius: 10rpx;
  width: 110rpx;
  height: 62rpx;
  background: linear-gradient(159deg,#6699CC 11%, #5699CC 94%);
  border-radius: 20px;
  box-shadow: 0px 0px 6px 0px rgba(30, 167, 247, 0.81);
}

.comment-input-container .add-image{
  height: 100rpx;
  width: 15%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-image: url('../../static/icon/add-img.png');
  background-size: 80% 80%;
  background-position: 50%;
  background-repeat: no-repeat;
}


.hei{  
  margin-top: 50px;  
  height: 20rpx;  
} 

.text-content{
  width: 60%;
  margin-left: 20rpx;
  margin-right: 20rpx;
  background: #f1f1f1;
  border-radius: 20px;
  padding: 25rpx 15rpx;
  font-size: 25rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  color: #7d7d7d;
}

/**post css**/
.post-container{
  position:fixed;
  right:18rpx;
  bottom:20rpx;
  z-index:100;
  width: 120prx;
  height: 120prx;
}

.post-container .wall{
  position: absolute;
  z-index: 105;
  width: 90rpx;
  height: 90rpx;
  border-radius: 45rpx;
  background: white;
  right: 10rpx;
  bottom: 30rpx;
}

.post image{
  width: 120rpx;
  height: 120rpx;
  position: relative;
  z-index: 110;
}
</style>
