import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

import StatisticsView from '@/views/StatisticsView.vue'

import DatasetView from '@/views/datasetpages/DatasetView.vue'
import ImportDataset from '@/views/datasetpages/ImportDataset.vue'
import RawDatasetView from '@/views/datasetpages/RawDatasetView.vue'
import CleanedDatasetView from '@/views/datasetpages/CleanedDatasetView.vue'

import ClassificationView from '@/views/ClassificationView.vue'

import AboutView from '@/views/AboutView.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/statistics'
    },
    {
      path: '/statistics',
      name: 'statistics',
      component: StatisticsView,
      meta: {
        title: 'Statistik'
      }
    },
    {
      path: '/dataset',
      name: 'dataset',
      component: DatasetView,
      redirect: to => {
        return { name: 'raw-dataset' }
      },
      children: [
        {
          path: 'raw',
          name: 'raw-dataset',
          component: RawDatasetView,
          meta: {
            title: 'Raw Dataset'
          }
        },
        {
          path: 'cleaned',
          name: 'cleaned-dataset',
          component: CleanedDatasetView,
          meta: {
            title: 'Cleaned Dataset'
          }
        },
        {
          path: 'import-dataset',
          name: 'import-dataset',
          component: ImportDataset,
          meta: {
            title: 'Import Dataset'
          }
        }
      ]
    },
    {
      path: '/classification',
      name: 'classification',
      component: ClassificationView,
      meta: {
        title: 'Klasifikasi'
      }
    },
    {
      path: '/about',
      name: 'about',
      component: AboutView,
      meta: {
        title: 'Tentang Aplikasi'
      }
    }
  ],
})

router.afterEach((to) => {
  const defaultTitle = 'Sentimen App'
  document.title = to.meta.title || defaultTitle
})

export default router
