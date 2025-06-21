export default defineNuxtRouteMiddleware((to, from) => {
  // Mengecek token di localStorage
  if (process.client) {
    const token = localStorage.getItem('token');
    if (!token) {
      return navigateTo('/login');
    }
  }
});