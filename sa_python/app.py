from flask import Flask, request, jsonify
import pandas as pd
import torch
from transformers import AutoTokenizer, AutoModelForSequenceClassification
import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_validate
from sklearn.svm import LinearSVC
from sklearn.metrics import accuracy_score, classification_report
from imblearn.over_sampling import SMOTE
from imblearn.pipeline import Pipeline as ImbPipeline
import numpy as np
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix


app = Flask(__name__)

MODEL_NAME = "Aardiiiiy/indobertweet-base-Indonesian-sentiment-analysis"
tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSequenceClassification.from_pretrained(MODEL_NAME)

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
model.to(device)



id_to_label = {
    0: "negative",
    1: "neutral",
    2: "positive"
}

@app.route("/classify", methods=["POST"])
def classify():
    try:
        # Ambil data dari request
        data = request.get_json()
        tweets = data.get("tweets", [])

        # Ubah ke DataFrame untuk analisis/debug
        df = pd.DataFrame(tweets)

        print("\n=== Data yang diterima dari Laravel ===")
        print(df.head())  # tampilkan 5 baris pertama
        print("Jumlah data:", len(df))

        predictions = []
        for text in df['tweet']:
            #Tokenisasi
            inputs = tokenizer(text, return_tensors = "pt", padding = True, truncation = True, max_length = 512).to(device)

            #Inferensi
            with torch.no_grad():
                outputs = model(**inputs)
                logits = outputs.logits
                predicted_class_id = torch.argmax(logits, dim=1).item()
                predictions.append(id_to_label[predicted_class_id])
        
        df['predicted_classification'] = predictions

        results = df.to_dict(orient = 'records')
        return jsonify({
            'status': 'success',
            'results': results
        })

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

# === Endpoint: Train ===
@app.route("/train", methods=["POST"])
def train():
    try:
        data = request.get_json()
        tweets = data.get("tweets", [])
        if not tweets:
            return jsonify({"status": "error", "message": "Tidak ada data untuk training"}), 400

        # 1. DataFrame
        df = pd.DataFrame(tweets)
        X = df["tweet"].astype(str)
        y = df["label"].astype(str)

        # 2. Split 80:20 (stratified)
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, stratify=y, random_state=42
        )

        # 3. Buat pipeline: TF-IDF -> SMOTE -> LinearSVC
        pipeline = ImbPipeline([
            ("tfidf", TfidfVectorizer(max_features=5000)),
            ("smote", SMOTE(random_state=42)),
            ("svm", LinearSVC(class_weight="balanced", random_state=42, max_iter=10000))
        ])

        # 4. Train
        pipeline.fit(X_train, y_train)

        # 5. Evaluasi di test set
        y_pred = pipeline.predict(X_test)

        acc = accuracy_score(y_test, y_pred)
        prec = precision_score(y_test, y_pred, pos_label="positive", zero_division=0)
        rec = recall_score(y_test, y_pred, pos_label="positive", zero_division=0)
        f1 = f1_score(y_test, y_pred, pos_label="positive", zero_division=0)
        cm = confusion_matrix(y_test, y_pred, labels=["negative", "positive"]).tolist()

        # 6. Simpan pipeline
        joblib.dump(pipeline, "svm_pipeline.pkl")

        # 7. Top words dari LinearSVC
        top_words = {}
        svm = pipeline.named_steps["svm"]
        tfidf = pipeline.named_steps["tfidf"]
        if hasattr(svm, "coef_"):
            coefs = svm.coef_[0]
            feature_names = np.array(tfidf.get_feature_names_out())
            top10_idx = np.argsort(coefs)[-10:]
            bottom10_idx = np.argsort(coefs)[:10]
            top_words["positive"] = feature_names[top10_idx].tolist()
            top_words["negative"] = feature_names[bottom10_idx].tolist()

        return jsonify({
            "status": "success",
            "message": "Training selesai dengan SMOTE + train/test split",
            "train_size": len(X_train),
            "test_size": len(X_test),
            "accuracy": acc,
            "precision": prec,
            "recall": rec,
            "f1_score": f1,
            "confusion_matrix": {
                "labels": ["negative", "positive"],
                "matrix": cm
            },
            "top_words": top_words
        })

    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({"status": "error", "message": str(e)}), 500


# === Endpoint: Evaluate ===
@app.route("/evaluate", methods=["POST"])
def evaluate():
    try:
        data = request.get_json()
        tweets = data.get("tweets", [])
        if not tweets:
            return jsonify({"status": "error", "message": "Tidak ada data untuk evaluasi"}), 400

        df = pd.DataFrame(tweets)
        X = df["tweet"].astype(str)
        y_true = df["label"].astype(str)

        # 1. Load pipeline
        pipeline = joblib.load("svm_pipeline.pkl")

        # 2. Prediksi
        y_pred = pipeline.predict(X)

        # 3. Metrik
        accuracy = accuracy_score(y_true, y_pred)
        precision = precision_score(y_true, y_pred, pos_label="positive", zero_division=0)
        recall = recall_score(y_true, y_pred, pos_label="positive", zero_division=0)
        f1 = f1_score(y_true, y_pred, pos_label="positive", zero_division=0)
        cm = confusion_matrix(y_true, y_pred, labels=["negative", "positive"]).tolist()

        # 4. Top words lagi
        top_words = {}
        svm = pipeline.named_steps["svm"]
        tfidf = pipeline.named_steps["tfidf"]
        if hasattr(svm, "coef_"):
            coefs = svm.coef_[0]
            feature_names = np.array(tfidf.get_feature_names_out())
            top10_idx = np.argsort(coefs)[-10:]
            bottom10_idx = np.argsort(coefs)[:10]
            top_words["positive"] = feature_names[top10_idx].tolist()
            top_words["negative"] = feature_names[bottom10_idx].tolist()

        return jsonify({
            "status": "success",
            "total_data": len(df),
            "accuracy": accuracy,
            "precision": precision,
            "recall": recall,
            "f1_score": f1,
            "confusion_matrix": {
                "labels": ["negative", "positive"],
                "matrix": cm
            },
            "top_words": top_words
        })

    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({"status": "error", "message": str(e)}), 500