import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

import StatisticsView from '@/views/StatisticsView.vue'

import DatasetView from '@/views/datasetpages/DatasetView.vue'
import ImportDataset from '@/views/datasetpages/ImportDataset.vue'
import RawDatasetView from '@/views/datasetpages/RawDatasetView.vue'
import CleanedDatasetView from '@/views/datasetpages/CleanedDatasetView.vue'

import PreprocessingView from '@/views/preprocessingpages/PreprocessingView.vue'
import PreprocessingCleaningView from '@/views/preprocessingpages/PreprocessingCleaningView.vue'
import PreprocessingCasefoldedView from '@/views/preprocessingpages/PreprocessingCasefoldedView.vue'
import PreprocessingFixedwordsView from '@/views/preprocessingpages/PreprocessingFixedwordsView.vue'
import PreprocessingStopwordRemovalView from '@/views/preprocessingpages/PreprocessingStopwordRemovalView.vue'
import PreprocessingStemmedView from '@/views/preprocessingpages/PreprocessingStemmedView.vue'

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
      path: '/preprocessing',
      name: 'preprocessing',
      component: PreprocessingView,
      redirect: to => {
        return { name: 'preprocessing-cleaning' }
      },
      children: [
        {
          path: 'cleaning',
          name: 'preprocessing-cleaning',
          component: PreprocessingCleaningView,
          meta: {
            title: 'preprocessing cleaning'
          }
        },
        {
          path: 'casefolding',
          name: 'preprocessing-casefolding',
          component: PreprocessingCasefoldedView,
          meta: {
            title: 'preprocessing casefolding'
          }
        },
        {
          path: 'fixed-words',
          name: 'preprocessing-fixedword',
          component: PreprocessingFixedwordsView,
          meta: {
            title: 'preprocessing fixed words'
          }
        },
        {
          path: 'stopword-removal',
          name: 'preprocessing-stopwordremoval',
          component: PreprocessingStopwordRemovalView,
          meta: {
            title: 'preprocessing stopword removal'
          }
        },
        {
          path: 'stemming',
          name: 'preprocessing-stemming',
          component: PreprocessingStemmedView,
          meta: {
            title: 'preprocessing stemming'
          }
        },
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
