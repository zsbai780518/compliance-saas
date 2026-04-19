<template>
  <view class="container">
    <!-- Logo -->
    <view class="logo">
      <image src="/static/images/logo.png" mode="aspectFit"></image>
      <text class="title">企业合规培训平台</text>
    </view>
    
    <!-- 登录表单 -->
    <view class="form">
      <view class="form-item">
        <input 
          type="number" 
          v-model="form.mobile" 
          placeholder="请输入手机号"
          maxlength="11"
          class="input"
        />
      </view>
      
      <view class="form-item">
        <input 
          type="password" 
          v-model="form.password" 
          placeholder="请输入密码"
          class="input"
        />
      </view>
      
      <view class="form-item code-row">
        <input 
          type="text" 
          v-model="form.smsCode" 
          placeholder="短信验证码"
          maxlength="6"
          class="input code-input"
        />
        <button 
          class="code-btn" 
          :disabled="countdown > 0"
          @click="sendSmsCode"
        >
          {{ countdown > 0 ? `${countdown}s` : '获取验证码' }}
        </button>
      </view>
      
      <button class="submit-btn" @click="handleLogin">登录</button>
      
      <view class="actions">
        <text class="link" @click="goRegister">没有账号？立即注册</text>
        <text class="link" @click="wxLogin">微信快捷登录</text>
      </view>
    </view>
    
    <!-- 底部说明 -->
    <view class="footer">
      <text class="tips">登录即代表您同意《用户协议》和《隐私政策》</text>
    </view>
  </view>
</template>

<script>
import { apiLogin, apiSendSms } from '@/utils/request.js'

export default {
  data() {
    return {
      form: {
        mobile: '',
        password: '',
        smsCode: ''
      },
      countdown: 0
    }
  },
  onLoad() {
    // 检查是否已登录
    const token = uni.getStorageSync('token')
    if (token) {
      this.checkToken(token)
    }
  },
  methods: {
    // 发送短信验证码
    async sendSmsCode() {
      if (!this.form.mobile || this.form.mobile.length !== 11) {
        uni.showToast({ title: '请输入正确的手机号', icon: 'none' })
        return
      }
      
      const res = await apiSendSms({ mobile: this.form.mobile })
      if (res.code === 200) {
        uni.showToast({ title: '验证码已发送', icon: 'success' })
        this.startCountdown()
      } else {
        uni.showToast({ title: res.msg, icon: 'none' })
      }
    },
    
    // 登录
    async handleLogin() {
      if (!this.form.mobile) {
        uni.showToast({ title: '请输入手机号', icon: 'none' })
        return
      }
      if (!this.form.password) {
        uni.showToast({ title: '请输入密码', icon: 'none' })
        return
      }
      
      const res = await apiLogin({
        mobile: this.form.mobile,
        password: this.form.password
      })
      
      if (res.code === 200) {
        // 保存 Token 和用户信息
        uni.setStorageSync('token', res.data.token)
        uni.setStorageSync('userInfo', res.data.user)
        
        uni.showToast({ title: '登录成功', icon: 'success' })
        
        // 跳转到首页
        setTimeout(() => {
          uni.switchTab({ url: '/pages/home/home' })
        }, 1500)
      } else {
        uni.showToast({ title: res.msg, icon: 'none' })
      }
    },
    
    // 检查 Token 有效性
    async checkToken(token) {
      // TODO: 调用验证接口
      uni.switchTab({ url: '/pages/home/home' })
    },
    
    // 去注册
    goRegister() {
      uni.navigateTo({ url: '/pages/login/register' })
    },
    
    // 微信登录
    wxLogin() {
      // #ifdef MP-WEIXIN
      uni.login({
        provider: 'weixin',
        success: (loginRes) => {
          console.log('微信登录 code:', loginRes.code)
          // TODO: 调用后端微信登录接口
        }
      })
      // #endif
      
      // #ifdef APP-PLUS || H5
      uni.showToast({ title: '请在微信内打开', icon: 'none' })
      // #endif
    },
    
    // 倒计时
    startCountdown() {
      this.countdown = 60
      const timer = setInterval(() => {
        this.countdown--
        if (this.countdown <= 0) {
          clearInterval(timer)
        }
      }, 1000)
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  padding: 60rpx 40rpx;
  min-height: 100vh;
  background: linear-gradient(180deg, #1890ff 0%, #096dd9 100%);
}

.logo {
  text-align: center;
  margin-bottom: 80rpx;
  
  image {
    width: 160rpx;
    height: 160rpx;
    margin-bottom: 20rpx;
  }
  
  .title {
    display: block;
    font-size: 36rpx;
    color: #fff;
    font-weight: bold;
  }
}

.form {
  background: #fff;
  border-radius: 16rpx;
  padding: 40rpx;
  
  .form-item {
    margin-bottom: 30rpx;
    
    .input {
      width: 100%;
      height: 88rpx;
      background: #f5f5f5;
      border-radius: 8rpx;
      padding: 0 24rpx;
      font-size: 28rpx;
    }
  }
  
  .code-row {
    display: flex;
    align-items: center;
    
    .code-input {
      flex: 1;
      margin-right: 20rpx;
    }
    
    .code-btn {
      width: 200rpx;
      height: 88rpx;
      line-height: 88rpx;
      font-size: 26rpx;
      background: #1890ff;
      color: #fff;
      border: none;
      border-radius: 8rpx;
      
      &[disabled] {
        background: #d9d9d9;
      }
    }
  }
  
  .submit-btn {
    width: 100%;
    height: 88rpx;
    line-height: 88rpx;
    background: linear-gradient(90deg, #1890ff, #096dd9);
    color: #fff;
    font-size: 32rpx;
    border-radius: 8rpx;
    border: none;
    margin-top: 40rpx;
  }
  
  .actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30rpx;
    
    .link {
      font-size: 26rpx;
      color: #1890ff;
    }
  }
}

.footer {
  margin-top: 60rpx;
  text-align: center;
  
  .tips {
    font-size: 24rpx;
    color: rgba(255,255,255,0.7);
  }
}
</style>
