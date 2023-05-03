<template>
	<view class="settle-info">
	      <image class="user-avatar-border" src="../../static/icon/border.png"></image>
	      <button class="avatar-wrapper" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
	        <image class="settle-avatar" :src="user.face" style="width: 100%;height: 100%;"></image>
	      </button> 
	      <view class="input">
	        <view class="tab">昵称</view>
	        <input class="data-input" @input="bindinputname" :value="user.username" placeholder="请输入昵称"/>
	      </view>
		  <view class="input">
		    <view class="tab">学校</view>
		    <input type="text" class="data-input" @input="bindinputschool" :value="user.school" placeholder="请输入学校"/>
		  </view>
		  <view class="input">
		    <view class="tab">学院</view>
		    <input type="text" class="data-input" @input="bindinputcollege" :value="user.college" placeholder="请输入学院"/>
		  </view>
		  <view class="input">
			  <view class="tab">性别</view>
			  <radio-group @change="handleChange">
				  <radio color="#6699CC" value="1" :checked="user.gender == '1'">男</radio>
				  <radio color="#6699CC" value="2" :checked="user.gender == '2'">女</radio>
			  </radio-group>
		  </view>
	      <view class="set-input">
	        <view class="set-ttile">个性签名</view>
	        <textarea 
	        maxlength="128"
	        :value="user.signature"
	        class='textarea-content'
	        placeholder="个性签名"    
	        @input="getTextContent"/>
	      </view>
	      <view class="set-save"><view class="set-save-button" @tap="UserInfoSave"><view>保存修改</view></view></view>
	</view>
</template>

<script>
	var _self,userId,random;
	var sign = require('../../commons/sign.js');
	export default {
		data() {
			return {
				user : {}
			}
		},
		onLoad() {
			_self = this;
			userId = uni.getStorageSync('SUID');
			random = uni.getStorageSync('SRAND');
			sign.sign(this.apiServer);
		},
		onShow : function() {
			// 加载用户信息
		    uni.request({
		        url: this.apiServer + 'my&m=info',
		        method: 'POST',
		        header: {'content-type' : "application/x-www-form-urlencoded"},
		        data: {
		            uid    : userId,
		            random : random
		        },
		        success: res => {
					console.log(res);
		            if(res.data.status == 'ok'){
		                this.user = res.data.data;
		            }
		        }
		    });
		},
		methods: {
			bindinputname(e){
				this.user.username = e.detail.value; // 获取微信昵称
			},
			bindinputschool(e){
				this.user.school = e.detail.value; // 获取学校信息
			},
			bindinputcollege(e){
				this.user.college = e.detail.value; //获取学院信息
			},
			onChooseAvatar(e){
				this.user.face = e.detail.avatarUrl;//获取头像信息
				uni.uploadFile({
						url      : _self.apiServer + 'uploadimg',
						filePath : _self.user.face,
						name     : 'file',
						success: (uploadFileRes) => {
							uploadFileRes = JSON.parse(uploadFileRes.data);
							if(uploadFileRes.status != 'ok'){
								uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
								return false;
							}
							//返回图片地址
							_self.user.face = _self.staticServer + uploadFileRes.data;
						},
						fail: (e) => {
							console.log(e);
							uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
						}
					});
			},
			handleChange(e){
				this.user.gender = e.detail.value;//获取性别信息
			},
			getTextContent(e){
				this.user.signature = e.detail.value;//获取签名
			},
			UserInfoSave(){
				
				var sign = uni.getStorageSync('sign');
				uni.request({
					url: _self.apiServer + 'updateuser&m=index',
					method: 'POST',
					header: {'content-type' : "application/x-www-form-urlencoded"},
					data: {
						userid : userId,
						name   : _self.user.username,
						face   : _self.user.face,
						school : _self.user.school,
						college : _self.user.college,
						gender : _self.user.gender,
						signature : _self.user.signature,
						sign   : sign
					},
					success: res => {
						// 跳转
						console.log(res);
						if(res.data.status == 'ok'){
							console.log(4);
							uni.switchTab({
								url:'/pages/my/my'
							})
						}
						
					},
					fail: (e) => {
						console.log(e);
					}
				});
			}
		}
	}
</script>

<style>
/** 个人资料设置 **/
.settle-info{
  width: 100%;
  display: flex;
  flex-direction: column;
  margin-top: 50rpx;
}

.avatar-wrapper{
  width: 120rpx;
  height: 120rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 50rpx;
  border: 5rpx solid #303030;
  border-radius: 40rpx;
  padding: 0rpx 0rpx;
}


.user-avatar-border{
  width: 90rpx;
  height: 90rpx;
  position: absolute;
  z-index: 200;
  left: 295rpx;
  top: 30rpx;
}

.settle-avatar{
  width: 50rpx;
  height: 50rpx;
}

.input{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 50rpx;
}

.tab{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 32rpx;
  font-family: SF Pro Text, SF Pro Text-Heavy;
  font-weight: 800;
  text-align: center;
  color: #303030;
  line-height: 32rpx;
  margin-bottom: 30rpx;
}

.data-input{
  width: 600rpx;
  border: 6rpx solid #303030;
  border-radius: 20rpx;
}

.input input{
  padding: 15rpx 15rpx;
}

.set-input{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.set-ttile{
  width: 100%;
  font-size: 32rpx;
  font-family: SF Pro Text, SF Pro Text-Heavy;
  font-weight: 800;
  text-align: center;
  color: #303030;
  line-height: 32rpx;
  margin-bottom: 30rpx;
}

.textarea-content{
  width: 600rpx;
  height: 160rpx;
  border: 6rpx solid #303030;
  border-radius: 20rpx;
  padding: 15rpx 15rpx;
  font-size: 28rpx;
  font-family: SF Pro Text, SF Pro Text-Regular;
  font-weight: 400;
  text-align: LEFT;
  color: #808080;
  line-height: 28rpx;
}

.set-save{
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 150rpx;
}

.set-save-button{
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 200rpx;
  height: 70rpx;
  background: linear-gradient(132deg,#6699cc 0%, #6699cc 100%);
  border-radius: 50rpx;
  box-shadow: 0px 0px 10rpx 0px rgba(30, 117, 247, 0.69); 
  font-size: 30rpx;
  font-family: SF Pro Text, SF Pro Text-Regular;
  font-weight: 400;
  color: #604708;
  line-height: 30rpx;
}
</style>
