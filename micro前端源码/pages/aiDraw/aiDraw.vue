<template>
		<view class="content">
			<view class="login_from">
				<view class="login_from_input">
					<view class="login_from_name">正tags:</view>
					<view class="login_from_fun">
						<textarea maxlength="-1" 
						          v-model="promptTags" 
						          fixed="true"
						          placeholder="请输入英文正提示词"
						          cursor-spacing="10"
						          auto-height/>
					</view>
				</view>
				<view class="login_from_input">
					<view class="login_from_name">负tags:</view>
					<view class="login_from_fun">
						<textarea maxlength="-1" 
						          v-model="negativeTags" 
						          fixed="true"
						          placeholder="请输入英文负提示词"
						          cursor-spacing="10"
						          auto-height/>
					</view>
				</view>
				<view class="login_from_input">
					  <view class="login_from_name">模型:</view>
					  <radio-group class="chose" @change="handleChange">
						  <radio color="#6699CC" value="1" :checked="models == '1'">基础模型</radio>
						  <radio color="#6699CC" value="2" :checked="models == '2'">国风模型</radio>
					  </radio-group>
				</view>
				<image v-if="imgUrl" :src="imgUrl" :data-url="imgUrl" mode="widthFix" @tap="showImgs"></image>
				<view class="login_from_dl">
					<button @click="enterTags">点击填写推荐tags</button>
				</view>
				
				<view class="login_from_dl">
					<button @click="getAiDraw">开始AI绘画</button>
				</view>
			</view>
		</view>
</template>

<script>
	var _self, Random,error;
	export default {
		data() {
			return {
				promptTags : '',
				negativeTags : '',
				imgUrl: '',
				models: 1
			};
		},
		methods:{
			handleChange(e){
				this.models = e.detail.value;//获取选择模型信息
			},
			bindInputPrompt(e){
				this.promptTags = e.detail.value; 
			},
			bindInputNegative(e){
				this.negativeTags = e.detail.value; 
			},
			enterTags(){
				this.promptTags = "masterpiece, best quality";
				this.negativeTags = "nsfw, lowres, bad anatomy, bad hands, text, error, missing fingers,extra digit, fewer digits, cropped, worst quality, low quality, normal quality, jpeg artifacts, signature, watermark, username, blurry";
			},
			showImgs : function(e){
				var currentUrl = e.currentTarget.dataset.url;
				var imgsNeedShow = [];
				imgsNeedShow.push(this.imgUrl);
				uni.previewImage({
					urls    :imgsNeedShow,
					current :currentUrl,
					longPressActions: {
					  itemList: imgsNeedShow,
					  success: (data) => {
					  	//先下载到本地获取临时路径
					    uni.downloadFile({
					      url: this.src,
					      success: (res) => {
					        console.log('downloadFile success, res is', res.tempFilePath)
					        //将临时路径保存到相册，即可在相册中查看图片
					        uni.saveImageToPhotosAlbum({
					          filePath: res.tempFilePath, //不支持网络地址
					          success: function () {
					            uni.showToast({
					              title: '保存图片到相册成功',
					              position: 'bottom'
					            });
					          }
					        });
					      },
					      fail: (err) => {
					        console.log('downloadFile fail, err is:', err)
					      }
					    })
					  },
					  fail: function(err) {
					    console.log(err.errMsg);
					  }
					}
				});
			},

			getAiDraw(){
				uni.showLoading({'title':"加载中..."});
				if(error == 1){
					uni.showToast({
					    title: '请先登录',
					    icon: 'none',
					    duration: 2000
					});
				}else{
					uni.showToast({
					    title: '暂未开放！',
					    icon: 'none',
					    duration: 2000
					});
					/* if(_self.models == 1){_self.models = 'anything-v5-PrtRE.safetensors[7f96a1a9ca]'}
					if(_self.models == 2){_self.models = 'GuoFeng3.ckpt [74c61c3a52]'}
					uni.request({
						url: _self.apiServer + 'getAiDraw&m=txt2img',
						method: 'POST',
						header: {'content-type' : "application/x-www-form-urlencoded"},
						data:{
							promptTags : _self.promptTags,
							negativeTags : _self.negativeTags,
							random: Random,
							models: _self.models
						},
						success:function(res){
							console.log(res);
							if(res.data.status == 'ok'){
								_self.imgUrl = res.data.data;
								uni.hideLoading();
							}else{
								uni.showToast({
								    title: '请求失败!',
								    icon: 'none',
								    duration: 2000
								});
							}
						}
					}); */
				}
				
			}
		},
		onLoad:function(){
			_self = this;
			Random = uni.getStorageSync('SRAND');
			if(!Random){
				error = 1;
			}
			else{
				error = 0;
			}
		},
		onShow:function(){
			this.promptTags = '';
			this.negativeTags = '';
		}
		}
</script>

<style>
.login_from{ width: 100%; height: auto; box-sizing: border-box; padding: 40upx 8%; margin-top: 80upx; }

.login_from_input{ width: 100%; height:auto; display: flex; flex-direction: row; justify-content: space-between; align-items: center; border-bottom: 1px #eee solid; padding: 50upx 0px; margin: 0px auto;  } 
.chose{width: 100%; display: flex; justify-content: space-between;}
.login_from_name{ width: 20%; }
.login_from_fun{ width: 80%; display: flex; flex-direction: row; justify-content: flex-end;  }
.login_from_fun input{ width: 100%; text-align: left; font-size: 14px;  }


.login_from_dl{ width: 100%; height: 50px; line-height: 50px; margin-top: 50upx;   }
.login_from_dl button{ width: 100%; height: 50px; line-height: 50px; background: #6699cc; color: #fff; border-radius: 0px; }


</style>
