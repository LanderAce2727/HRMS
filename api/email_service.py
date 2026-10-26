import os
import smtplib
import secrets
from email.message import EmailMessage
from dotenv import load_dotenv

load_dotenv()

def send_email(to, subject, body):
    msg = EmailMessage()
    msg["Subject"] = subject
    msg["From"] = os.environ["EMAIL_USER"]
    msg["To"] = to
    msg.set_content(body)
    with smtplib.SMTP_SSL("smtp.gmail.com", 465) as server:
        server.login(os.environ["EMAIL_USER"], os.environ["EMAIL_PASS"])
        server.send_message(msg)

def generate_code():
    return f"{secrets.randbelow(1_000_000):06d}"   # 6-digit code

def send_verification_code(to, code):
    send_email(to, "Your HRMS verification code",
               f"Your code is {code}. It expires in 10 minutes.\n"
               "If you didn't request this, ignore this email.")

def send_notification(to, subject, message):
    send_email(to, subject, message)