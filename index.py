import assemblyai as aai
import sys
from dotenv import load_dotenv
import os


def convert_milliseconds(ms):
    hours = ms // 3600000
    minutes = (ms % 3600000) // 60000
    seconds = (ms % 60000) // 1000
    return f"{hours:02}:{minutes:02}:{seconds:02}"

# Load the .env file
load_dotenv()

aai.settings.api_key = os.getenv("ASSEMBLYAI_API_KEY")


if __name__ == "__main__":
    config = aai.TranscriptionConfig(speaker_labels=True, speakers_expected=int(sys.argv[2]), language_code=sys.argv[3])
    transcriber = aai.Transcriber()
    if(sys.argv[3] == "ar"):
        config = aai.TranscriptionConfig(speakers_expected=int(sys.argv[2]), language_code=sys.argv[3], speech_model=aai.SpeechModel.nano)
        transcript = transcriber.transcribe(
        sys.argv[1],
        config=config
        )
        print(transcript.text)
    else:
        transcript = transcriber.transcribe(
        sys.argv[1],
        config=config
        )
        for utterance in transcript.utterances:
            print(f"Speaker {utterance.speaker}: {utterance.text}\nTime: {convert_milliseconds(utterance.start)}-{convert_milliseconds(utterance.end)}\n")
