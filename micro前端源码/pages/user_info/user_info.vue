<template>
	<view class="container">
		<view class="bg-content">
		    <image mode="center" class="bg" src="../../static/pic/bg.png"></image>
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
			<view class="follow-content">
			    <view :class="follow == 0 ? 'follow-icon' : 'not-follow-icon' " @tap="followUser" v-if="follow==0"><view>关注</view></view>
			    <view :class="follow == 0 ? 'follow-icon' : 'not-follow-icon' " @tap="cancelFollowUser" v-if="follow!=0"><view>已关注</view></view>
			    <view class="follow-icon" @tap="gotoMessage" v-if="random_loc != random_tar"><view>私信</view></view>
			</view>
		</view>
		<view class="grace-box-banner" style="margin:10rpx 0;">
		    <view class="garce-items" @tap="openUserPost">
		        <view class="line1">{{user.postCount}}</view>
		        <view class="line2">帖子</view>
		    </view>
		    <view class="garce-items" @tap="gotoFansList">
		        <view class="line1">{{user.fans_num}}</view>
		        <view class="line2">粉丝</view>
		    </view>
		    <view class="garce-items" @tap="gotoFollowList">
		        <view class="line1">{{user.follow_num}}</view>
		        <view class="line2">关注</view>
		    </view>
		</view>
	</view>
</template>

<script>
	var _self,random,follow_random,error;
	export default {
		data() {
			return {
				level : 0,
				user:{},
				my:{},
				arts : [],
				follow : 0,
				random_loc:'',
				random_tar:''
			}
		},
		onLoad(option) {
			_self = this;
			follow_random = option.random;
			random = uni.getStorageSync('SRAND');
			_self.random_loc = random;
			_self.random_tar = follow_random;
		},
		onShow : function() {
			// 加载用户信息
		    uni.request({
		        url: this.apiServer + 'my&m=getUserInfo',
		        method: 'POST',
		        header: {'content-type' : "application/x-www-form-urlencoded"},
		        data: {
		            random : follow_random
		        },
		        success: res => {
					console.log(res);
		            if(res.data.status == 'ok'){
		                _self.user = res.data.data;
						_self.level = Math.floor(this.user.experience/100);
		            }
					else{error = 1;}
		        }
		    });
			uni.request({
			    url: this.apiServer + 'my&m=getUserInfo',
			    method: 'POST',
			    header: {'content-type' : "application/x-www-form-urlencoded"},
			    data: {
			        random : random
			    },
			    success: res => {
					console.log(res);
			        if(res.data.status == 'ok'){
			            _self.my = res.data.data;
			        }
					else{error = 1;}
			    }
			});
			setTimeout(()=>{
				var Follow = _self.my.follow;
				if(JSON.stringify(Follow).charAt(1) !== "[")
				{
					Follow = [];
				}
				else{
					Follow = JSON.parse(Follow);
				}
				Follow.forEach(function(value,index,array){
					if(array[index] == follow_random)
					_self.follow = 1;
				})
			},500)
		},
		methods: {
			openUserPost:function(){
				uni.navigateTo({
					url:'/pages/myPosts/myPosts?random='+follow_random
				})
			},
			gotoMessage:function(){
				uni.navigateTo({
					url: '../message/message?random=' + follow_random + '&username=' + _self.user.username
				})
			},
			gotoFollowList:function(){
				uni.navigateTo({
					url: '../follow_list/follow_list?random='+follow_random
				})
			},
			gotoFansList:function(){
				uni.navigateTo({
					url: '../fan_list/fan_list?random='+follow_random
				})
			},
			cancelFollowUser(){
				this.follow = 0;
				var fans = this.user.fans;
				var follow = this.my.follow;
				fans = JSON.parse(fans);
				follow = JSON.parse(follow);
				fans.forEach(function(value,index,array){
					if(array[index] == random)
					array.splice(index,1);
				})
				follow.forEach(function(value,index,array){
					if(array[index] == follow_random)
					array.splice(index,1);
				})
				uni.request({
					url: _self.apiServer + 'my&m=follow',
				    method: 'POST',
				    header: {'content-type' : "application/x-www-form-urlencoded"},
					data: {
				       follow_random: follow_random,
					   random: random,
					   fans: JSON.stringify(fans),
					   follow: JSON.stringify(follow),
					   is: 0
				    },
					success:function(res){
						console.log(res);
						if(res.data.status == 'ok'){
							_self.user = res.data.data;
							_self.level = Math.floor(_self.user.experience/100);
				        }
					}
				});
			},
			followUser(){
				this.follow = 1;
				var fans = this.user.fans;
				var follow = this.my.follow;
				if(error == 1){
					this.follow = 0;
					uni.showToast({
					    title: '请先登录',
					    icon: 'none',
					    duration: 2000
					});
				}
				if(JSON.stringify(fans).charAt(1) !== "[")
				{
					fans = [];
				}
				else{
					fans = JSON.parse(fans);
				}
				if(JSON.stringify(follow).charAt(1) !== "[")
				{
					follow = [];
				}
				else{
					follow = JSON.parse(follow);
				}
				fans.push(random);
				follow.push(follow_random);
				uni.request({
					url: _self.apiServer + 'my&m=follow',
				    method: 'POST',
				    header: {'content-type' : "application/x-www-form-urlencoded"},
					data: {
				       follow_random: follow_random,
					   random: random,
					   fans: JSON.stringify(fans),
					   follow: JSON.stringify(follow),
					   is: 1
				    },
					success:function(res){
						console.log(res);
						if(res.data.status == 'ok'){
							_self.user = res.data.data;
							_self.level = Math.floor(_self.user.experience/100);
				        }
					}
				});
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
.follow-content{
  width: 50%;
  margin: 0 auto;
  display: flex;
  flex-direction: row;
  margin-top: 20rpx;
  justify-content: space-around;
}

.follow-icon{
  width: 100rpx;
  height: 50rpx;
  font-size: 25rpx;
  font-family: OPPOSans, OPPOSans-Heavy;
  font-weight: 800;
  text-align: CENTER;
  color: #303030;
  background: #6699cc;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 25rpx;
}

.not-follow-icon{
  width: 100rpx;
  height: 50rpx;
  font-size: 25rpx;
  font-family: OPPOSans, OPPOSans-Heavy;
  font-weight: 800;
  text-align: CENTER;
  color: #303030;
  background: #a7cff7;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 25rpx;
}
/* 用户具体资料 */
.grace-box-banner{padding:20upx 0; display:flex; justify-content:space-around; flex-wrap:nowrap; overflow:hidden; background:#FFF;}
.grace-box-banner .garce-items{width:25%; border-right:1px solid #F1F2F3; justify-content:center; line-height:1.4em; text-align:center;}
.grace-box-banner .garce-items:last-child{border:none;}
.grace-box-banner .garce-items view{justify-content:center; text-align:center;}
.grace-box-banner .garce-items text{font-size:22rupx; color:#666;}
.grace-box-banner .line1{font-size:36upx; line-height:60upx; overflow:hidden;}
.grace-box-banner .line1 text{font-size:26upx; color:#666; line-height:65upx; margin-left:5upx;}
.grace-box-banner .line2{font-size:26upx; color:#666; line-height:32upx;}

</style>
