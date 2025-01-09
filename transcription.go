package main

import (
	"fmt"
	"os/exec"
)

func transcription(filepath string, speakers_expected string, language_code string) string {
	//speakersStr := fmt.Sprintf("%d", speakers_expected)

	cmd := exec.Command("Python3", "./index.py", filepath, speakers_expected, language_code)
	output, err := cmd.CombinedOutput()

	if err != nil {
		fmt.Println("Error " + err.Error())
		return ""
	}
	fmt.Println(string(output))
	return string(output)
}
