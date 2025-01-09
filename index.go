package main

import (
	"fmt"
	"net/http"
)

func root(w http.ResponseWriter, req *http.Request) {
	fmt.Fprint(w, "Welcome to our API")
}
func main() {
	mainRouter := http.NewServeMux()

	transcriptRouter := http.NewServeMux()
	transcriptRouter.HandleFunc("/transcript", func(w http.ResponseWriter, req *http.Request) {
		/*
			Todo:
				we want to send a file (video or audio) to this router. Then call the trancript function

				REMEMBER: transcript requires 3 parameters -> transcription("filename", speakers, language_code)
		*/
		if req.Method == http.MethodPost {
			req.ParseForm()
			fmt.Fprintf(w, "Received POST data: %v\n", req.Form)
		} else {
			http.Error(w, "Invalid request method", http.StatusMethodNotAllowed)
		}
		//fmt.Fprintf(w, transcription("./german.wav", 5, "de"))
	})

	mainRouter.Handle("/transcript", transcriptRouter)

	mainRouter.HandleFunc("/", root)
	fmt.Println("Server started at http://localhost:8085")
	http.ListenAndServe(":8085", mainRouter)
}
