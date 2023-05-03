<template>
	<view>
		<view class="content">
			
			<view class="login_img">
				<button class="login_button" open-type="chooseAvatar" @chooseavatar="onChooseavatar">
				<image :src="avatarUrl"></image>
				</button>
			</view>
			
			<view class="login_from">
				<view class="login_from_input">
					<view class="login_from_name">昵称</view>
					<view class="login_from_fun">
						<input type="nickname" :value="nickName" placeholder="请输入昵称" @input="bindinputname" />
					</view>
				</view>
		
				<view class="login_from_input">
					<view class="login_from_name">学校</view>
					<view class="login_from_fun">
						<input type="text" :value="school" placeholder="请输入学校" @input="bindinputschool" />
					</view>
				</view>
				
				<view class="login_from_input">
					<view class="login_from_name">学院</view>
					<view class="login_from_fun">
						<input type="text" :value="college" placeholder="请输入学院" @input="bindinputcollege" />
					</view>
				</view>
				
				<view class="login_from_dl">
					<button @click="getUserInfo">注册</button>
				</view>
				
				<view class="logo_xieyi">
					<label @click="moutcl" :class="gouxSta?'cuIcon-squarecheckfill':'cuIcon-square'"></label>
					<view class="logo_text">请勾选并阅读<text>《注册协议》</text>及<text>《隐私协议》</text></view>
				</view>
				
			</view>
			
		</view>
	</view>
</template>

<script>
	var _self, session_key, openid, pageOptions;
	var sign = require('../../commons/sign.js');
	var defaultAvatarUrl = 'https://thirdwx.qlogo.cn/mmopen/vi_32/POgEwh4mIHO4nibH0KlMECNjjGxQUq24ZEaGT4poC6icRiccVGKSyXwibcPq4BWmiaIGuG1icwxaQX6grC9VemZoJ8rg/132';
	export default {
		data() {
			return {
				avatarUrl : defaultAvatarUrl,
				nickName : '',
				school : '',
				college : '',
				gouxSta:false
			};
		},
		methods:{
			moutcl(){
				if(this.gouxSta == false){
					this.gouxSta = true;
				}
				else{
					this.gouxSta = false;
				}
			},
			bindinputname(e){
				this.nickName = e.detail.value; // 获取微信昵称
			},
			bindinputschool(e){
				this.school = e.detail.value; 
			},
			bindinputcollege(e){
				this.college = e.detail.value; 
			},
			onChooseavatar(e){
				this.avatarUrl = e.detail.avatarUrl;
			},
			getUserInfo : function(){
				var sign = uni.getStorageSync('sign');
				//将头像临时路径上传到服务器
				/* _self.avatarUrl = _self.uploadimg(_self.avatarUrl,_self.apiServer); */
				uni.uploadFile({
						url      : _self.apiServer + 'uploadimg',
						filePath : _self.avatarUrl,
				/* 		header:{"Content-Type": "mutipart/form-data"}, */
						name     : 'file',
						success: (uploadFileRes) => {
							console.log(uploadFileRes);
							uploadFileRes = JSON.parse(uploadFileRes.data);
							if(uploadFileRes.status != 'ok'){
								uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
								return false;
							}
							//返回图片地址
							_self.avatarUrl = _self.staticServer + uploadFileRes.data;
						},
						fail: (e) => {
							console.log(e);
							uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
						}
					})
				//上传数据
				setTimeout(()=>{
					uni.request({
						url: _self.apiServer+'member&m=login',
						method: 'POST',
						header: {'content-type' : "application/x-www-form-urlencoded"},
						data: {
							openid : openid,
							name   : _self.nickName,
							face   : _self.avatarUrl,
							school : _self.school,
							college : _self.college,
							sign   : sign
						},
						success: res => {
							console.log(res);
							res = res.data;
							uni.showToast({title:"注册成功"});
							uni.setStorageSync('SUID' , res.data.id + '');
							uni.setStorageSync('SRAND', res.data.random + '');
							uni.setStorageSync('SNAME', res.data.username + '');
							uni.setStorageSync('SFACE', res.data.face + '');
							uni.setStorageSync('SSCHOOL', res.data.school + '');
							uni.setStorageSync('SCOLLEGE', res.data.college + '');
							uni.setStorageSync('SGENDER', res.data.gender + '');
							// 跳转
							if(pageOptions.backtype == 1){
								uni.redirectTo({url:pageOptions.backpage});
							}else{
								uni.switchTab({url:pageOptions.backpage});
							}
						},
						fail: (e) => {
							console.log(e);
						}
					});
				},500)
			}
		},
		onLoad:function(options){
			sign.sign(this.apiServer);
			pageOptions = options;
			_self = this;
			uni.login({
				success:function(res){
					//console.log(res);
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
		}
</script>

<style>
.login_img{ width: 100%; height: auto; margin-top: 90upx;}
.login_button{width: 200upx; height: 200upx; display: flex; flex-direction: column; align-items: center; margin: 0 auto; border-radius: 50%;}
.login_img image{ width: 200upx; height: 200upx; }

.login_from{ width: 100%; height: auto; box-sizing: border-box; padding: 40upx 8%; margin-top: 80upx; }

.login_from_input{ width: 100%; height:auto; display: flex; flex-direction: row; justify-content: space-between; align-items: center; border-bottom: 1px #eee solid; padding: 50upx 0px; margin: 0px auto;  } 

.login_from_name{ width: 20%; }
.login_from_fun{ width: 80%; display: flex; flex-direction: row; justify-content: flex-end;  }
.login_from_fun input{ width: 100%; text-align: left; font-size: 14px;  }


.login_from_dl{ width: 100%; height: 50px; line-height: 50px; margin-top: 150upx;   }
.login_from_dl button{ width: 100%; height: 50px; line-height: 50px; background: #6699cc; color: #fff; border-radius: 0px; }

.logo_xieyi{ width: 100%; height: 40px; line-height: 40px; display: flex; flex-direction: row; margin-top: 30upx; align-items: center; color: #444; font-size: 14px; justify-content: center; }
.logo_xieyi label{ font-size: 18px; margin-right: 1%; display: block; width: 15px; height: 15px; }

.cuIcon-squarecheckfill{ color: #6699cc; }
.logo_text text{ color: #6699cc; }
.cuIcon-squarecheckfill{ background: #6699cc; position: relative; border: 2px #ccc solid; box-sizing: border-box; border-radius: 3px; }
.cuIcon-square{ background: #fff; border: 2px #ccc solid; box-sizing: border-box; border-radius: 3px; }
</style>
