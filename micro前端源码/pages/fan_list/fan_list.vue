<template>
	<view class="message-container">
	  <view class='message-content'>
	    <!-- 粉丝列表 -->
	    <view class="friend-container">
	      <view class="container-content">
	        <view class="content-item" 
	              v-for="(item, index) in list" 
	              :key="index"
	              @tap="openUserInfo" 
	              :data-random="item.random">
	          <view class="user-info">
	              <image class="avatar-border" src="../../static/icon/border.png"></image>
	              <image class="avatar" :src="item.face"></image>
	            <view class="user-name">
	              <view class="user-nickname">{{item.username}}</view>
	            </view>
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
			var routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			var curRoute = routes[routes.length - 1].route //获取当前页面路由
			var curParam = routes[routes.length - 1].options;
			Random = curParam.random;
			this.getFansList();
		},
		methods: {
			openUserInfo: function(e){
				var random = e.currentTarget.dataset.random;
				uni.navigateTo({
					url: '../user_info/user_info?random='+random
				})
			},
			getFansList:function(){
				uni.request({
				    url: this.apiServer + 'my&m=getFansList&random='+Random,
				    method: 'GET',
				    success: res => {
						console.log(res);
				        if(res.data.status == 'ok'){
				            _self.list = res.data.data;
				        }
						else{
							uni.showToast({
							    title: '还没有任何粉丝',
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
</style>
