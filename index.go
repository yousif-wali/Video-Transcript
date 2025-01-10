package main

import (
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"time"
)

func corsMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type")

		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusNoContent)
			return
		}
		next.ServeHTTP(w, r)
	})
}

func root(w http.ResponseWriter, req *http.Request) {
	fmt.Fprint(w, "Welcome to our API")
}

func transcriptHandler(w http.ResponseWriter, req *http.Request) {
	if req.Method != http.MethodPost {
		http.Error(w, "Invalid request method", http.StatusMethodNotAllowed)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	req.ParseForm()

	// Validate required form fields
	Username, userOk := req.Form["Username"]
	speakersExpected, speakersOk := req.Form["speakers_expected"]
	language, langOk := req.Form["language"]

	if !userOk || !speakersOk || !langOk || len(Username[0]) == 0 || len(speakersExpected[0]) == 0 || len(language[0]) == 0 {
		http.Error(w, "Missing required fields", http.StatusBadRequest)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	err := req.ParseMultipartForm(10 << 20) // 10 MB limit
	if err != nil {
		http.Error(w, "Error parsing form data", http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	file, handler, err := req.FormFile("file")
	if err != nil {
		http.Error(w, "Error retrieving the file", http.StatusBadRequest)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}
	defer file.Close()

	// Generate timestamp and create a directory with timestamp
	timestamp := time.Now().Format("2006-01-02_15-04-05")
	uploadDir := filepath.Join("uploads", Username[0], timestamp)

	if err := os.MkdirAll(uploadDir, os.ModePerm); err != nil {
		http.Error(w, "Error creating directory", http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	// Save the uploaded file
	savePath := filepath.Join(uploadDir, handler.Filename)
	outFile, err := os.Create(savePath)
	if err != nil {
		http.Error(w, "Error saving the file", http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}
	defer outFile.Close()

	_, err = io.Copy(outFile, file)
	if err != nil {
		http.Error(w, "Error copying the file", http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	audioOutputPath := filepath.Join(uploadDir, "audio.wav")
	transcriptFileName := filepath.Join(uploadDir, "transcript.txt")

	// Use channels for concurrency
	done := make(chan error)

	// Start audio extraction concurrently
	go func() {
		cmd := exec.Command("ffmpeg", "-i", savePath, "-vn", "-acodec", "pcm_s16le", "-ar", "44100", "-ac", "2", audioOutputPath)
		cmdOutput, cmdErr := cmd.CombinedOutput()
		if cmdErr != nil {
			done <- fmt.Errorf("Error converting file with ffmpeg: %v\nOutput: %s", cmdErr, cmdOutput)
			w.WriteHeader(http.StatusInternalServerError)
			return
		}
		done <- nil
	}()

	// Wait for audio extraction to finish before transcription
	if err := <-done; err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	// Perform transcription (assuming transcription is synchronous)
	transcripter := transcription(audioOutputPath, speakersExpected[0], language[0])

	// Save the transcript concurrently
	go func() {
		saveFileErr := os.WriteFile(transcriptFileName, []byte(transcripter), 0644)
		done <- saveFileErr
	}()

	// Wait for transcript to finish writing
	if err := <-done; err != nil {
		http.Error(w, "Error writing transcript to file", http.StatusInternalServerError)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}
	w.WriteHeader(http.StatusOK)
	fmt.Fprintf(w, "%s\n", transcripter)
}

func main() {
	mainRouter := http.NewServeMux()

	mainRouter.HandleFunc("/", root)
	mainRouter.HandleFunc("/transcript", transcriptHandler)

	fmt.Println("Server started at http://0.0.0.0:8085")
	http.ListenAndServe("0.0.0.0:8085", corsMiddleware(mainRouter))
}
