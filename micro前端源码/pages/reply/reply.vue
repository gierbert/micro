<template>
	<view class="message-container">
	  <view class='message-content'>
	    <!-- 消息列表 -->
	    <view class="friend-container">
	      <view class="container-content">
	        <view class="content-item" 
	              v-for="(item, index) in list" 
	              :key="index"
	              @tap="openMessage" 
	              :data-random="item.random">
	          <view class="user-info">
	              <image class="avatar-border" src="../../static/icon/border.png"></image>
	              <image class="avatar" :src="item.face"></image>
	            <view class="user-name">
	              <view class="user-nickname">{{item.username}}</view>
				  <view class="date-time">{{item.createtime}}</view>
	            </view>
	          </view>
			  <view class="content">
				<view class="inbox-count" v-if="item.newMesNum != 0 && item.newMesNum != null">
					<view>{{item.newMesNum}}</view>
				</view>
				{{item.message}}
			  </view>
	        </view> 
	      </view>
	    </view>
	
	  </view>
	
	</view>
</template>

<script>
	var _self, Random;
	export default {
		data() {
			return {
				list:[]
			}
		},
		onLoad : function(option){
			_self = this;
			Random = option.random;
		},
		onShow:function(){
			this.list = [];
			this.getReplyList();
		},
		methods: {
			openMessage: function(e){
				var random = e.currentTarget.dataset.random;
				uni.navigateTo({
					url: '../message/message?random='+random
				})
			},
			getReplyList:function(){
				uni.request({
				    url: this.apiServer + 'my&m=getReplyList&random='+Random,
				    method: 'GET',
				    success: res => {
						console.log(res);
				        if(res.data.status == 'ok'){
				            _self.list = res.data.data;
							_self.list.forEach(function(value,index,array){
								if(array[index].message == null){
									array[index].message = '【图片】';
								}
								if(array[index].message.length >15){
									array[index].message = array[index].message.slice(0,15) + "...";
								}else{
									array[index].message = array[index].message.slice(0,15);
								}
								
							})
				        }
						else{
							uni.showToast({
							    title: '还没有消息！',
							    icon: 'none',
							    duration: 2000
							});
						}
				    }
				});
			}
		}
	}
</script>

<style>
page{
  background: #ffffff;
}

.message-container{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.message-content{
  width: 100%;
  display: flex;
  flex-direction: column;
  border-top-style:solid;
  border-width:1rpx;
  border-color: #F0F8FF;
  align-items: center;
}

/** 关注列表 **/
.friend-container{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 15rpx;
}

.friend-container .container-content{
  width: 100%;
  display: flex;
  flex-direction: column;
  background: white;
  align-items: center;
}

.container-content .content-item{
  display: flex;
  width: 90%;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  height:120rpx;
  background: #ffffff;
  border-radius: 20rpx;
  box-shadow: 0px 4rpx 10rpx 0px rgba(0,0,0,0.20); 
  margin-top: 15rpx;
}

.user-info{
  display: flex;
  flex-direction: row;
  align-items: center;
  position: relative;
}

.user-info .user-name{
  margin-left: 20rpx;
  display: flex;
  flex-direction: column;
}


.user-info .avatar{
  width: 70rpx;
  height: 70rpx;
  border: 4rpx solid #303030;
  margin-left: 20rpx;
  border-radius: 25rpx;
}

.avatar-border{
  width: 60rpx;
  height: 60rpx;
  position: absolute;
  z-index: 200;
  left: 6rpx;
  top: -15rpx;
}

.user-nickname{
  font-size: 28rpx;
  font-family: PingFangSC, PingFangSC-Medium;
  font-weight: 800;
  text-align: left;
  color: #303030;
}

.date-time{
  font-size: 22rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #8c8c8c;
}
.content{
  display: flex; 
  flex-direction: row;
  width: 50%;
  font-size: 28rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  color: #8c8c8c;
}
.inbox-count{
  width: 40rpx;
  height: 40rpx;
  background: #ff6060;
  color: white;
  font-size: 25rpx;
  z-index: 300;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 23rpx;
}
</style>
