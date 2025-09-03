from flask import Flask, request, jsonify
import classifier
import evaluate_svm   # ✅ ganti dari svm_model ke evaluate_svm
from sklearn.model_selection import cross_validate
import numpy as np
import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.svm import SVC

app = Flask(__name__)

# Load SVM model saat server Flask start
svm_bundle = None

@app.route("/classify", methods=["POST"])
def classify():
    try:
        data = request.json or {}
        tweets = data.get("tweets", [])

        # Ambil hanya teks
        texts = [tweet["text"] for tweet in tweets if "text" in tweet]

        # Klasifikasi → hasil berupa list ["negative", "positive", ...]
        predictions = classifier.run_classification(texts)

        # Gabungkan hasil dengan ID
        combined = []
        for tweet, pred in zip(tweets, predictions):
            combined.append({
                "id": tweet["id"],
                "predicted_classification": pred
            })
        print("DEBUG predictions:", predictions)

        return jsonify({"status": "success", "results": combined})

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500
    

@app.route("/train", methods=["POST"])
def train():
    global svm_bundle
    data = request.json or {}
    dataset = [
        (item.get("text"), item.get("label"))
        for item in data.get("tweets", [])
        if item.get("text") and item.get("label")
    ]

    if not dataset:
        return jsonify({
            "status": "error",
            "message": "Dataset kosong atau tidak valid!"
        }), 400

    try:
        # Latih model & simpan ke file
        svm_bundle = evaluate_svm.train_and_save(dataset, "svm_model.pkl")
        return jsonify({
            "status": "success",
            "message": "Model trained & saved",
            "total_data": len(dataset)
        })
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": f"Gagal melatih model: {str(e)}"
        }), 500


# @app.route("/train", methods=["POST"])
# def train():
#     global svm_bundle
#     data = request.json or {}
#     dataset = [
#         (item.get("text"), item.get("label"))
#         for item in data.get("tweets", [])
#         if item.get("text") and item.get("label")
#     ]

#     if not dataset:
#         return jsonify({
#             "status": "error",
#             "message": "Dataset kosong atau tidak valid!"
#         }), 400

#     try:
#         texts = [t for t, l in dataset]
#         labels = [l for t, l in dataset]

#         # Vectorizer
#         vectorizer = TfidfVectorizer(
#             max_features=10000,
#             ngram_range=(1,2),
#             min_df=3,
#             max_df=0.8
#         )
#         X = vectorizer.fit_transform(texts)

#         # Model
#         model = SVC(kernel="linear", probability=True)

#         # Cross Validation (5-fold)
#         scores = cross_validate(
#             model,
#             X,
#             labels,
#             cv=5,
#             scoring=["accuracy", "precision_weighted", "recall_weighted", "f1_weighted"],
#             return_train_score=False,
#             n_jobs=-1
#         )

#         # Hitung rata-rata skor
#         results = {
#             "accuracy": float(np.mean(scores["test_accuracy"])),
#             "precision": float(np.mean(scores["test_precision_weighted"])),
#             "recall": float(np.mean(scores["test_recall_weighted"])),
#             "f1_score": float(np.mean(scores["test_f1_weighted"]))
#         }

#         # Fit ulang model dengan seluruh data
#         model.fit(X, labels)
#         svm_bundle = {"model": model, "vectorizer": vectorizer}
#         joblib.dump(svm_bundle, "svm_model.pkl")

#         return jsonify({
#             "status": "success",
#             "message": "Model trained with cross-validation & saved",
#             "total_data": len(dataset),
#             "cv_results": results
#         })
#     except Exception as e:
#         return jsonify({
#             "status": "error",
#             "message": f"Gagal melatih model: {str(e)}"
#         }), 500




@app.route("/evaluate", methods=["POST"])
def evaluate():
    global svm_bundle
    try:
        print("DEBUG: masuk /evaluate")

        # Load model kalau belum ada
        if svm_bundle is None:
            print("DEBUG: loading model dari file...")
            try:
                svm_bundle = evaluate_svm.load_model("svm_model.pkl")
            except FileNotFoundError:
                return jsonify({"status": "error", "message": "Model belum dilatih!"}), 400

        data = request.json or {}
        tweets = data.get("tweets", [])
        print(f"DEBUG: jumlah tweets masuk = {len(tweets)}")

        texts = [t.get("text") for t in tweets if "text" in t]
        labels = [t.get("label") for t in tweets if "label" in t]

        if not texts or not labels:
            return jsonify({"status": "error", "message": "Dataset evaluasi kosong/invalid!"}), 400

        # Evaluasi
        result = evaluate_svm.evaluate(texts, labels, svm_bundle)
        print("DEBUG: hasil evaluasi =", result)

        if not isinstance(result, dict):
            result = {}

        result["top_words"] = evaluate_svm.get_top_words(svm_bundle, n=15) or []
        result["total_data"] = len(texts)

        return jsonify(result)

    except Exception as e:
        print("ERROR di /evaluate:", str(e))
        return jsonify({
            "status": "error",
            "message": f"Evaluate gagal: {str(e)}"
        }), 500



