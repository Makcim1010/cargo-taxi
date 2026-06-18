import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginForm from '../components/LoginForm.vue'
import RegisterForm from '../components/RegisterForm.vue'
import ProfileView from '../views/ProfileView.vue';
import FeesView from '../views/FeesView.vue';
import NewsView from '../views/NewsView.vue';

const routes = [
  { path: '/', component: HomeView },
  { path: '/login', component: LoginForm },
  { path: '/register', component: RegisterForm },
  { path: '/profile', component: ProfileView, meta: { requiresAuth: true } },
  { path: '/fees', component: FeesView },
  { path: '/news', component: NewsView},
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('access_token')
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})

export default router