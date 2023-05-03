<template>
	<view>
		<!-- 标题 -->
	    <view :class="['grace-article-title', graceSkeleton == 'ing' ? 'grace-skeleton' : '']">{{article.title}}</view>
		<!-- 作者信息 -->
	    <view class="grace-article-author-line">
	        <view :class="['grace-article-author', graceSkeleton == 'ing' ? 'grace-skeleton' : '']" >
	            <image :src="article.face" mode="widthFix" @tap="openuserinfo"></image>
	            <view class="author-name">{{article.username}}</view>
	        </view>
	        <view>{{article.createtime}}</view>
	    </view>
		<!-- 文章内容 -->
	    <view class="grace-article-contents">
	        <block v-for="(item, index) in artContents" :key="index">
	        <view :class="['img-item', graceSkeleton == 'ing' ? 'grace-skeleton' : '']" v-if="item.type == 'image'">
	            <image :src="item.content" :data-url="item.content" mode="widthFix" @tap="showImgs"></image>
	        </view>
	        <view :class="['text-item', graceSkeleton == 'ing' ? 'grace-skeleton' : '']" v-if="item.type == 'text'">{{item.content}}</view>
	        </block>
	    </view>
		<!-- 评论 -->
		<view class="comContainer">
		    <h4>评论：</h4>
		    <view class="readly" id="commentsBox">
		        <view class="readly-item" v-for="(item, index) in commentsList" :key="index">
					<view class="face" @tap="openUserInfo" :data-clickartrandom="item.random">
						<image :src="item.face"></image>
					</view>
		            <view class="nickName">{{ item.username }}</view>
		            <view class="timeReply">
		                <view class="time">
		                    <text>{{ item.com_createtime }}</text>
		                </view>
		                <view class="reply" v-if="userid == item.com_uid">
		                    <!-- v-if="userId==replyId" -->
		                    <text @tap="handleDelReply" :data-id="item.id" :data-index="index">删除</text>
		                </view>
		            </view>
		            <view class="content">{{ item.com_content }}</view>
		        </view>
		    </view>
			<view class='comment-input-container'>
			  <textarea class='text-content'
			            maxlength="1024" 
			            v-model='content' 
			            fixed="true"
			            placeholder="请输入内容"
			            cursor-spacing="10"
			            auto-height/>
			  <view class='send-button' @tap='handleConfirm'>
			    <view>发送</view>
			  </view>
			</view>
		    <!-- <textarea v-model="inpVal" placeholder="请输入内容"  @confirm="handleConfirm"></textarea>
		    <view class="confirmBtn" @tap="handleConfirm">提交</view> -->
		</view>
	</view>
</template>

<script>
	var artid, _self,userId,Random;
	export default {
		data() {
			return {
				article : [], //文章基础信息
				artContents : [], // 文章项目
				graceSkeleton : 'ing',
				content: '',//输入评论
				userid:'',
				commentsList: []//评论
			}
		},
		methods: {
			openUserInfo: function(e){
				var random = e.currentTarget.dataset.clickartrandom;
				uni.navigateTo({
					url: '../user_info/user_info?random='+random
				})
			},
			openuserinfo(){
				uni.navigateTo({
					url: '../user_info/user_info?random='+this.article.random
				})
			},
			// 重新所有获取评论数据
			getComments() {
			    const _self = this;
			    return new Promise(resolve=>{
			        uni.request({
			            url: _self.apiServer + 'comment&m=getComment',
			            data: {
			                artid: artid
			            },
			            header: {
			                'content-type': 'application/x-www-form-urlencoded'
			            },
			            method: 'GET',
			            success: res => {
							var comment =  res.data.data;
							 _self.commentsList = comment;
			                resolve();
			            }
			        });
			    })
			},
			handleDelReply(e){
				var id = e.currentTarget.dataset.id;
				var index = e.currentTarget.dataset.index;
				uni.request({
				    url: _self.apiServer + 'comment&m=deleteComment',
				    data: {
				        random: Random,
				        uid: userId,
				        com_id: id
				    },
				    header: {'content-type' : "application/x-www-form-urlencoded"},
				    method: 'POST',
				    success: res => {
						console.log(res);
						if(res.data.status == 'ok'){
						    uni.showToast({title: "已删除", icon:"none"});
						    _self.commentsList.splice(index, 1);
						}else{
						    uni.showToast({title: "删除失败", icon:"none"});
						}
				    }
				});
			},
			//上传评论
			handleConfirm() {
/* 			    const commentsBox = document.getElementById('commentsBox'); */
			    const _self = this;
			    if (userId) {
			        uni.request({
			            url: _self.apiServer + 'comment&m=putComment',
			            data: {
			                artid: artid,
			                content: _self.content,
			                userId: userId,
			            },
			            header: {'content-type' : "application/x-www-form-urlencoded"},
			            method: 'POST',
			            success: res => {
							console.log(res);
			                _self.getComments();/* .then(()=>{
			                    commentsBox.scrollTop = commentsBox.scrollHeight;
			                }); */
			                _self.content='';
			            }
			        });
			    } else {
			        uni.showToast({
			            title: '请先登录',
			            icon: 'none',
			            duration: 2000
			        });
			    }
			},
			//展示图片
			showImgs : function(e){
				var currentUrl = e.currentTarget.dataset.url;
				var imgsNeedShow = [];
				for(var i = 0; i < this.artContents.length; i++){
					if(this.artContents[i].type == 'image'){
						imgsNeedShow.push(this.artContents[i].content);
					}
				}
				uni.previewImage({
					urls    :imgsNeedShow,
					current :currentUrl
				});
			}
		},
	onLoad : function(option){
			_self = this;
			artid = option.artid;
			userId = uni.getStorageSync('SUID');
			Random = uni.getStorageSync('SRAND')
			_self.userid = userId;
			// 加载文章详情
	        uni.showLoading({title:""});
	        uni.request({
	            url: this.apiServer + 'posts&m=infoWithUser&artid='+artid,
	            method: 'GET',
	            data: {},
	            success: res => {
					console.log(res);
					var art = res.data.data;
					
					// 将文章内容转换成数组
	                var artContentItems = JSON.parse(art.content);
	                //
					// 首先规划骨架
	                this.artContents = []; 
	                for(var i = 0; i < artContentItems.length; i++){
	                    this.artContents.push({'type': artContentItems[i].type});
	                }
					setTimeout(function(){
						_self.article       = art;
						_self.artContents   = artContentItems;
						_self.graceSkeleton = 'end';
						uni.hideLoading();
					},500);
				}
			});
			this.getComments();
		}
	}
</script>

<style lang="scss">
.grace-skeleton{padding:10rpx 0; background:#F1F2F3; border-radius:16rpx;}
.grace-article-title{width: 100%; text-align: center; font-size:52rpx; line-height:1.5em; font-weight:700;}
.grace-article-author-line{margin:6rpx 24rpx; display:flex; flex-wrap:nowrap; justify-content:space-between;font-size:25rpx;}
.grace-article-author{display:flex; flex-wrap:nowrap;}
.grace-article-author image{width:56rpx; height:56rpx; border-radius:100%;}
.grace-article-author .author-name{line-height:56rpx; padding-left:10rpx; font-size:25rpx;}
.grace-article-author-line .btn{display:inline-block; height:56rpx; line-height:56rpx; border-radius:6rpx; padding:0 20rpx; background:#00B26A; color:#FFFFFF;}
.grace-article-info-line{margin:16rpx 24rpx; display:flex; flex-wrap:nowrap; justify-content:space-between;}
.grace-article-info-line view{color:#888; line-height:40rpx; font-size:24rpx;}
.grace-article-contents{margin:20rpx 0;}
.grace-article-contents .img-item{width:100%;}
.grace-article-contents .img-item image{width:100%;}
.grace-article-contents .text-item{margin:16rpx 24rpx; line-height:2.2em; font-size:32rpx; color:#2F2F2F;} 
.comContainer {
    box-sizing: border-box;
    padding: 20rpx;
    width: 100%;
    height: 100%;
    background-color: #fff;
    .readly {
        box-sizing: border-box;
        width: 100%;
        height: 30vh;
        padding: 15rpx;
        border: 2px solid #f2f2ff;
        margin: 20rpx 0;
        overflow: auto;
        .readly-item {
            box-sizing: border-box;
            background-color: #f6f6f6;
            margin: 10rpx 0;
            padding: 10rpx;
			.face{
				image{
					width:56rpx; height:56rpx; border-radius:100%;
				}
			}
            .nickName {
                width: 100%;
                height: 50rpx;
                font: 32rpx/50rpx '';
            }
            .timeReply {
                width: 100%;
                display: flex;
                justify-content: space-between;
                font: 24rpx/40rpx '';
                color: #666;
                .reply {
                    text {
                        font-size: 18rpx;
                        background-color: #ddd;
                        padding: 5rpx;
                        // border: 1px solid #999;
                        margin-right: 20rpx;
                        color: #666;
                    }
                }
            }
            .content {
                box-sizing: border-box;
                width: 100%;
                background-color: #fff;
                font: 28rpx/36rpx '';
                border-radius: 15rpx;
                padding: 10rpx;
                margin: 10rpx 0;
            }
        }
    }
    .confirmBtn {
        width: 120rpx;
        height: 50rpx;
        background-color: #009fe9;
        border-radius: 10rpx;
        color: #fff;
        text-align: center;
        margin: 20rpx 0;
        box-shadow: 2rpx 2rpx 5rpx #cccccc;
    }
}
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


</style>