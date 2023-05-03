<template>
	<view>
		<view class="content">
			
			<view class="login_img">
				<image src="../../static/icon/logo.jpg"></image>
			</view>
			<view class="login_text">
				Welcome Micro
			</view>
			<view class="login_from_dl">
				<button @click="login">登录</button>
			</view>
			
		</view>
	</view>
</template>

<script>
	var _self, session_key, openid, pageOptions;
	export default {
		data() {
			return {
				
			}
		},
		onLoad:function(options){
			pageOptions = options;
			_self = this;
			uni.login({
				success:function(res){
					console.log(res);
					uni.request({
						url: _self.apiServer+'member&m=codeToSession&code='+res.code,
						method: 'GET',
						success: res => {
							console.log(res);
							session_key = res.data.session_key;
			                openid      = res.data.openid;
						}
					});
				}
			})
		},
		methods: {
			login(){
					uni.request({
						url: _self.apiServer+'member&m=checkregister&openid='+openid,
						method: 'GET',
						success: res => {
							console.log(res);
							if(res.data.status == 'ok'){
								res = res.data;
								uni.showToast({title:"登录成功"});
								uni.setStorageSync('SUID' , res.data.id + '');
								uni.setStorageSync('SRAND', res.data.random + '');
								uni.setStorageSync('SNAME', res.data.username + '');
								uni.setStorageSync('SFACE', res.data.face + '');
								uni.setStorageSync('SSCHOOL', res.data.school + '');
								uni.setStorageSync('SCOLLEGE', res.data.college + '');
								uni.setStorageSync('SGENDER', res.data.gender + '');
								//跳转
								if(pageOptions.backtype == 1){
									uni.redirectTo({url:pageOptions.backpage});
								}else{
									uni.switchTab({url:pageOptions.backpage});
								}
							}
							else{
								uni.redirectTo({
									url:"../login/login?backpage="+pageOptions.backpage+"&backtype="+pageOptions.backtype
								});
							}
						},
						fail: (e) => {
							console.log(e);
						},
					});
				}
			}
		}
</script>

<style>
.login_img{display: flex; flex-direction: column; align-items: center; margin-top: 100rpx;}
.login_img image{ width: 200upx; height: 200upx;  border-radius: 50%;}
.login_text{width: 100%; text-align: center; margin-top: 50rpx;}
.login_from_dl{ width: 100%; height: 50px; line-height: 50px; margin-top: 150upx;   }
.login_from_dl button{ width: 50%; height: 50px; line-height: 50px; background: #6699cc; color: #fff; border-radius: 0px; }
</style>
