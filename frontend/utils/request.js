/**
 * API 请求封装
 */
const BASE_URL = 'https://your-domain.com/api'

// 获取 Token
const getToken = () => {
  return uni.getStorageSync('token') || ''
}

// 请求封装
const request = (options) => {
  return new Promise((resolve, reject) => {
    uni.request({
      url: BASE_URL + options.url,
      method: options.method || 'GET',
      data: options.data || {},
      header: {
        'Content-Type': 'application/json',
        'Authorization': getToken()
      },
      success: (res) => {
        if (res.statusCode === 200) {
          resolve(res.data)
        } else if (res.statusCode === 401) {
          // Token 过期，跳转登录
          uni.removeStorageSync('token')
          uni.removeStorageSync('userInfo')
          uni.reLaunch({ url: '/pages/login/login' })
          reject(res.data)
        } else {
          uni.showToast({ title: res.data.msg || '请求失败', icon: 'none' })
          reject(res.data)
        }
      },
      fail: (err) => {
        uni.showToast({ title: '网络错误', icon: 'none' })
        reject(err)
      }
    })
  })
}

// 认证相关 API
export const apiLogin = (data) => request({ url: '/auth/login', method: 'POST', data })
export const apiRegister = (data) => request({ url: '/auth/register', method: 'POST', data })
export const apiSendSms = (data) => request({ url: '/auth/sms_code', method: 'POST', data })
export const apiLogout = () => request({ url: '/auth/logout', method: 'POST' })

// 用户相关 API
export const apiGetProfile = () => request({ url: '/user/profile' })
export const apiUpdateProfile = (data) => request({ url: '/user/update', method: 'POST', data })
export const apiUploadIdCard = (filePath) => {
  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url: BASE_URL + '/auth/idcard_upload',
      filePath: filePath,
      name: 'id_card',
      header: { 'Authorization': getToken() },
      success: (res) => resolve(JSON.parse(res.data)),
      fail: (err) => reject(err)
    })
  })
}

// 课程相关 API
export const apiGetCourseList = (params) => request({ url: '/course/list', method: 'GET', data: params })
export const apiGetCourseDetail = (id) => request({ url: `/course/detail?course_id=${id}` })

// 学习相关 API
export const apiGetProgress = (courseId) => request({ url: `/learn/progress?course_id=${courseId}` })
export const apiRecordLearn = (data) => request({ url: '/learn/record', method: 'POST', data })
export const apiHeartbeat = (data) => request({ url: '/learn/heartbeat', method: 'POST', data })

export default request
