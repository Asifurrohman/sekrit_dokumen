<template>
    <div class="p-6 bg-white rounded-xl shadow-md w-full mx-auto">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Sentimen</h2>
        <apexchart type="donut" height="350" :options="chartOptions" :series="series"></apexchart>
        
        <h2 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Evaluasi Model</h2>
        <apexchart type="bar" height="350" :options="barChartOptions" :series="barSeries"></apexchart>
        
        <h2 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Confusion Matrix</h2>
        <apexchart type="heatmap" height="300" :options="cmOptions" :series="cmSeries"></apexchart>
        
        <h2 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Word Cloud</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Positive Words</h3>
                <WordCloud :words="positiveWords" :color="wordColor" />
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Negative Words</h3>
                <vue3-word-cloud style="width: 100%; height: 300px" :words="negativeWords" :color="wordColor"></vue3-word-cloud>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Vue3WordCloud from 'vue3-word-cloud';

const tweetData = ref([])
const statisticData = ref([])
const series = ref([0, 0])
const cmSeries = ref([])
const positiveWords = ref([])
const negativeWords = ref([])
const WordCloud = Vue3WordCloud


// Opsi chart
const chartOptions = ref({
    labels: ["Positif:", "Negatif:"],
    colors: ["#22c55e", "#ef4444"],
    legend: {
        position: "bottom"
    },
    plotOptions: {
        pie: {
            donut: {
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: "Total Data Sentimen",
                        formatter: () => series.value.reduce((a, b) => a + b, 0)
                    }
                }
            }
        }
    }
})

const barSeries = ref([
{
    name: "Score",
    data: [0, 0, 0, 0] // [Accuracy, Precision, Recall, F1]
}
])

const barChartOptions = ref({
    chart: { id: "evaluation-bar", toolbar: { show: false } },
    xaxis: { categories: ["Accuracy", "Precision", "Recall", "F1-Score"] },
    yaxis: { max: 1, tickAmount: 5 },
    plotOptions: { bar: { horizontal: false, columnWidth: "50%" } },
    dataLabels: { enabled: true, formatter: val => (val * 100).toFixed(2) + "%" },
    colors: ["#3b82f6"]
})

// Confusion matrix
const cmOptions = ref({
    chart: { toolbar: { show: false } },
    dataLabels: { enabled: true },
    xaxis: { categories: ["Pred: Negative", "Pred: Positive"] },
    yaxis: { categories: ["True: Negative", "True: Positive"] },
    plotOptions: {
        heatmap: {
            shadeIntensity: 0.5,
            colorScale: {
                ranges: [
                { from: 0, to: 50, color: "#fca5a5", name: "low" },
                { from: 51, to: 200, color: "#fdba74", name: "mid" },
                { from: 201, to: 500, color: "#86efac", name: "high" }
                ]
            }
        }
    }
})


const fetchStatisticData = async () => {
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets/all')
        
        tweetData.value = data.data.map(item => ({
            rawTweet: item.rawTweet,
            cleanedTweet: item.cleanedTweet,
            classification: item.classification || ''
        }))
        
        const totalPositif = tweetData.value.filter(tweet => tweet.classification === 'positive').length
        const totalNegatif = tweetData.value.filter(tweet => tweet.classification === 'negative').length
        series.value = [totalPositif, totalNegatif]
        chartOptions.value.labels = [`Positif: ${totalPositif}`, `Negatif: ${totalNegatif}`]
        
        // Hitung metrik evaluasi sederhana dari API atau hardcode untuk contoh
        // Misalnya ambil dari endpoint evaluasi
        const { data: evalData } = await axios.get('http://127.0.0.1:8000/api/machine-learning')
        statisticData.value = evalData.data.map(item => ({
            modelName: item.modelName,
            totalData: item.totalData,
            accuracy: item.accuracy,
            precision: item.precision,
            recall: item.recall,
            f1Score: item.f1Score,
            confusionMatrix: typeof item.confusionMatrix === "string" ? JSON.parse(item.confusionMatrix) : item.confusionMatrix,
            topWords: item.topWords
        }))
        
        
        if (statisticData.value.length > 0) {
            const latestModel = statisticData.value[statisticData.value.length - 1]
            
            barSeries.value[0].data = [
            latestModel.accuracy || 0,
            latestModel.precision || 0,
            latestModel.recall || 0,
            latestModel.f1Score || 0
            ]
            
            const cmData = latestModel.confusionMatrix
            const cm = cmData?.matrix || [[0,0],[0,0]]
            cmSeries.value = [
            { 
                name: "Actual Negative", 
                data: [
                { x: "Pred: Negative", y: cm[0][0] }, // True Negative
                { x: "Pred: Positive", y: cm[0][1] }  // False Positive
                ] 
            },
            { 
                name: "Actual Positive", 
                data: [
                { x: "Pred: Negative", y: cm[1][0] }, // False Negative
                { x: "Pred: Positive", y: cm[1][1] }  // True Positive
                ] 
            }
            ]
            
            let topWordsData = latestModel.topWords
            
            // kalau masih string, parse jadi object
            if (typeof topWordsData === "string") {
                try {
                    topWordsData = JSON.parse(topWordsData)
                } catch (e) {
                    console.error("Gagal parse topWords:", e)
                    topWordsData = { positive: [], negative: [] }
                }
            }
            
            positiveWords.value = (topWordsData?.positive || []).map(
            w => [w, Math.floor(Math.random() * 20) + 10]
            )
            
            negativeWords.value = (topWordsData?.negative || []).map(
            w => [w, Math.floor(Math.random() * 20) + 10]
            )
            
        }
        
    } catch(error){
        console.error('Gagal mengambil data statistik:', error)
    }
}

const wordColor = () => {
    const colors = ["#16a34a", "#2563eb", "#9333ea", "#ea580c", "#dc2626"]
    return colors[Math.floor(Math.random() * colors.length)]
}


onMounted(async () => {
    await fetchStatisticData()
})
</script>

<style scoped>

</style>