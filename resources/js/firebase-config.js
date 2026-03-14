import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyAg_JoZEfjUK5MrU_6MxrBOYWfFrGbYu_Y",
  authDomain: "flight-74000.firebaseapp.com",
  projectId: "flight-74000",
  storageBucket: "flight-74000.firebasestorage.app",
  messagingSenderId: "562917723042",
  appId: "1:562917723042:web:a2ea88f0f334f86cfd80fb",
  measurementId: "G-SCYGQ117WD"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase Authentication and get a reference to the service
export const auth = getAuth(app);

export default app;
