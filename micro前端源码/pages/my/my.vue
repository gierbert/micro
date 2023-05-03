<template>
	<view class="container">
		<view class="bg-content">
		    <image mode="center" class="bg" src="../../static/pic/bg.jpg"></image>
		    <view class="my-word" v-if="user.signature">
		      <view class="my-word-content">{{user.signature}}</view>
		    </view>
		    <view class="bg-null"></view>
		    <view class="avatar-container">
		      <image class="avatar" :src="user.face"></image>
		      <image class="header-avatar-border" src="../../static/icon/border.png"></image>
		      <image class="boy" v-if="user.gender == 1" src="../../static/icon/gender_boy.png"></image>
		      <image class="girl" v-if="user.gender == 2" src="../../static/icon/gender_girl.png"></image>
		    </view>
		</view>
		<view class="user-info">
		      <view class="username">
		        <view class="nickname">
					<view class="nickname-data">{{user.username}}</view>
					<view class="level">LV{{level}}</view>
				</view>
				<view class="school-info">
					<view class="school" v-if="user.school">{{user.school}}</view>
					<view class="school" v-if="user.college">{{user.college}}</view>
				</view>
		      </view>
		</view>
		<view class="grace-box-banner" style="margin:10rpx 0;">
		    <view class="garce-items" @tap="openMyPost">
		        <view class="line1">{{user.postCount}}</view>
		        <view class="line2">帖子</view>
		    </view>
		    <view class="garce-items" @tap="gotoFansList" >
		        <view class="line1">{{user.fans_num}}</view>
		        <view class="line2">粉丝</view>
		    </view>
		    <view class="garce-items" @tap="gotoFollowList" >
		        <view class="line1">{{user.follow_num}}</view>
		        <view class="line2">关注</view>
		    </view>
		    <view class="garce-items" @tap="gotoReply" >
		        <view class="line3">
					<view class="inbox-count" v-if="user.newMesNum != 0">
						<view>{{user.newMesNum}}</view>
					</view>
					消息
				</view>
		    </view>
		</view>
		<view class="inbox-content" @tap="openMessage">
		      <image src="../../static/icon/personal-poster.png"></image>
		</view>
	</view>
</template>

<script>
	var openid,session_key,_self,loginRes;
	export default {
		data() {
			return {
				level : 0,
				user:{},
				arts : []
			}
		},
		onLoad() {
			_self = this;
			loginRes = this.checkLogin('../my/my', '2');
			if(!loginRes){return false;}
		},
		onShow : function() {
			loginRes = this.checkLogin('../my/my', '2');
			if(!loginRes){return false;}
			// 加载用户信息
		    uni.request({
		        url: this.apiServer + 'my&m=info',
		        method: 'POST',
		        header: {'content-type' : "application/x-www-form-urlencoded"},
		        data: {
		            uid    : loginRes[0],
		            random : loginRes[1]
		        },
		        success: res => {
					console.log(res);
		            if(res.data.status == 'ok'){
		                this.user = res.data.data;
						this.level = Math.floor(this.user.experience/100);
		            }
		        }
		    });
		},
		methods: {
			gotoReply:function(){
				uni.navigateTo({
					url:'/pages/reply/reply?random='+loginRes[1]
				})
			},
			openMyPost:function(){
				uni.navigateTo({
					url:'/pages/myPosts/myPosts?random='+loginRes[1]
				})
			},
			gotoFollowList:function(){
				uni.navigateTo({
					url: '../follow_list/follow_list?random='+loginRes[1]
				})
			},
			gotoFansList:function(){
				uni.navigateTo({
					url: '../fan_list/fan_list?random='+loginRes[1]
				})
			},
			openMessage: function () {
			    uni.navigateTo({
			      url: '/pages/editInfo/editInfo'
			    })
			  }
		}
	}
</script>

<style>
.container{
  width: 100%;
  display: flex;
  flex-direction: column;
}
/* 用户信息 */
.bg-content{
  width: 100%;
  position:relative;
}

.bg{
  width: 100%;
  height: 600rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.bg-null{
  width: 100%;
  height: 100rpx;
  background: #ffffff;
  position: absolute;
  z-index: 100;
  bottom: -5rpx;
  border-radius: 50% 50% 0 0 / 90% 90% 0 0 ;
}

.my-word{
  position: absolute;
  z-index: 300;
  height: 130rpx;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  top: 250rpx;
}

.my-word-content{
  width: 50%;
  padding: 20rpx 50rpx;
  font-size: 25rpx;
  font-family: OPPOSans, OPPOSans-Regular;
  font-weight: 400;
  text-align: CENTER;
  color: #ffffff;
  line-height: 28rpx;
  background: rgba(0,0,0,0.10);
  border-radius: 20rpx;
  backdrop-filter: blur(3rpx);
}

.avatar-container{
  width: 100%;
  height: 150rpx;
  position: absolute;
  z-index: 500;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  bottom: 20rpx;
}

.avatar-container .avatar{
  width: 150rpx;
  height: 150rpx;
  border-radius:35rpx;
  box-shadow: 0px 0px 50rpx -10rpx rgba(193,193,193,0.71);
  border: 5rpx solid #303030; 
}

.header-avatar-border{
  width: 100rpx;
  height:100rpx;
  position: absolute;
  z-index: 600;
  bottom: 75rpx;
  left: 275rpx;
}

.girl{
  width: 55rpx;
  height:55rpx;
  position: absolute;
  z-index: 600;
  bottom: -5rpx;
  left: 290rpx;
}

.boy{
  width: 55rpx;
  height:55rpx;
  position: absolute;
  z-index: 600;
  bottom: -5rpx;
  left: 290rpx;
}
.user-info{
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-around;
}
.user-info{
	display: flex;
	flex-direction: column;
	align-content: center;
	justify-content: center;
}
.user-info .nickname{
	display: flex;
	flex-direction: row;
	align-content: center;
	justify-content: center;
}
.user-info .nickname .nickname-data{
	height: 60rpx;
	font-size: 40rpx;
	font-family: SF Pro Text, SF Pro Text-Bold;
	font-weight: 700;
	text-align: CENTER;
	color: #303030;
	line-height: 40rpx;
}

.user-info .nickname .level{
	margin-top: 10rpx;
	margin-bottom: 10rpx;
	margin-left: 20rpx;
	height: 30rpx;
	font-size: 25rpx;
	background: #6699cc;
	border-radius: 20rpx;
	box-shadow: 0px 0px 8rpx 2rpx rgba(22, 141, 238, 0.81); 
	text-align: center;
	line-height: 25rpx;
	color: white;
}
.user-info .school-info{
	display: flex;
	flex-direction: row;
	align-content: center;
	justify-content: center;
}
.user-info .school-info .school{
	margin-left: 25rpx;
	height: 30rpx;
	font-size: 25rpx;
	background: #6699cc;
	border-radius: 20rpx;
	box-shadow: 0px 0px 8rpx 2rpx rgba(22, 141, 238, 0.81); 
	text-align: center;
	line-height: 25rpx;
	color: white;
}
/* 用户具体资料 */
.grace-box-banner{padding:20upx 0; display:flex; flex-wrap:nowrap; overflow:hidden; background:#FFF;}
.grace-box-banner .garce-items{width:25%; border-right:1px solid #F1F2F3; justify-content:center; line-height:1.4em; text-align:center;}
.grace-box-banner .garce-items:last-child{border:none;}
.grace-box-banner .garce-items view{justify-content:center; text-align:center;}
.grace-box-banner .line1{font-size:36upx; line-height:60upx; overflow:hidden;}
.grace-box-banner .line2{font-size:26upx; color:#666; line-height:32upx;}
.grace-box-banner .line3{display: flex; flex-direction: row; font-size:35upx; color:#666; line-height:90upx;}
/* 编辑资料 */
.inbox-content{
  display: flex;
  flex-direction: column;
  width: 130rpx;
  height: 130rpx;
  align-items: center;
  justify-content: center;
  position: fixed;
  z-index: 100;
  right: 20rpx;
  bottom: 50rpx;
}
.inbox-content image{
  width: 120rpx;
  height: 120rpx;
  z-index: 200;
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
