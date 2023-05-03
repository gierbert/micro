<template>
	<view class='post-container'>
	    <view class='wall'></view>
	    <view class='post'>
	      <image src='../../static/icon/poster.png' @click='post'></image>
	    </view>
	</view>
</template>

<script>
	export default {
		name:'write',
		data() {
			return {
				
			}
		},
		methods: {
			post: function () {
			    uni.navigateTo({
			    	url: '/pages/post/post'
			    })
			  }
		}
	}
</script>

<style>
/** post css**/
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
