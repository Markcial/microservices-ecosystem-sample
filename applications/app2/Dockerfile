FROM python:2.7

RUN pip install --no-cache-dir flask requests

WORKDIR /app
VOLUME ["/app"]
EXPOSE 80

CMD ["python", "debug.py"]