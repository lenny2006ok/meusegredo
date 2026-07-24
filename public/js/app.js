const notificationBar = document.getElementById('notification-bar');
const themeToggle = document.getElementById('theme-toggle');
const shareButton = document.getElementById('share-button');
const reportButton = document.getElementById('report-button');
const reportModal = document.getElementById('report-modal');
const closeReport = document.getElementById('close-report');
const sendReport = document.getElementById('send-report');
const commentText = document.getElementById('comment-text');
const submitComment = document.getElementById('submit-comment');
const csrfTokenElement = document.querySelector('meta[name="csrf-token"]') || document.getElementById('csrf-token');
const csrfToken = csrfTokenElement ? csrfTokenElement.value || csrfTokenElement.content : null;

function showNotification(message) {
  if (!notificationBar) return;
  notificationBar.textContent = message;
  notificationBar.style.display = 'block';
  setTimeout(() => {
    notificationBar.style.display = 'none';
  }, 3200);
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('theme-light');
    document.body.classList.toggle('theme-dark');
    const theme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
    localStorage.setItem('theme', theme);
  });
}

window.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    document.body.classList.remove('theme-dark');
    document.body.classList.add('theme-light');
  }
});

if (shareButton) {
  shareButton.addEventListener('click', async () => {
    const url = window.location.href;
    try {
      await navigator.clipboard.writeText(url);
      showNotification('Link copiado para a área de transferência.');
    } catch (error) {
      showNotification('Não foi possível copiar o link.');
    }
  });
}

if (reportButton && reportModal) {
  reportButton.addEventListener('click', () => {
    reportModal.classList.add('open');
  });
}

if (closeReport) {
  closeReport.addEventListener('click', () => {
    reportModal.classList.remove('open');
  });
}

if (sendReport) {
  sendReport.addEventListener('click', async () => {
    const reason = document.getElementById('report-reason').value;
    const postId = window.location.pathname.split('/').pop();
    const response = await fetch('/api/report', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ postId, reason })
    });
    const result = await response.json();
    if (result.success) {
      showNotification('Denúncia enviada. Obrigado por ajudar a manter o site seguro.');
      reportModal.classList.remove('open');
    }
  });
}

if (submitComment) {
  submitComment.addEventListener('click', async () => {
    if (!commentText.value.trim()) {
      showNotification('Escreva um comentário antes de enviar.');
      return;
    }
    const postId = window.location.pathname.split('/').pop();
    const response = await fetch('/api/comment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ postId, text: commentText.value })
    });
    const data = await response.json();
    if (data.success) {
      showNotification('Comentário enviado com sucesso.');
      window.location.reload();
    }
  });
}

const likeButtons = document.querySelectorAll('[data-action="like"], [data-action="dislike"]');
likeButtons.forEach(button => {
  button.addEventListener('click', async () => {
    const postId = button.dataset.id;
    const action = button.dataset.action;
    const response = await fetch('/api/like', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ postId, action })
    });
    const data = await response.json();
    if (data.success) {
      showNotification('Interação registrada.');
      window.location.reload();
    } else {
      showNotification(data.message || 'Erro ao interagir.');
    }
  });
});

const replyButtons = document.querySelectorAll('.reply-button');
replyButtons.forEach(button => {
  button.addEventListener('click', () => {
    const id = button.dataset.id;
    const replyBox = document.createElement('div');
    replyBox.className = 'reply-box';
    replyBox.innerHTML = `
      <textarea id="reply-text-${id}" rows="3" placeholder="Responder anonimamente..."></textarea>
      <button class="button button-outline" data-parent="${id}">Enviar resposta</button>
    `;
    button.closest('.comment-card').appendChild(replyBox);
    replyBox.querySelector('button').addEventListener('click', async () => {
      const text = replyBox.querySelector('textarea').value;
      const postId = window.location.pathname.split('/').pop();
      const parentId = id;
      if (!text.trim()) {
        showNotification('Escreva uma resposta antes de enviar.');
        return;
      }
      const response = await fetch('/api/comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'CSRF-Token': csrfToken || '' },
        body: JSON.stringify({ postId, parentId, text })
      });
      const data = await response.json();
      if (data.success) {
        showNotification('Resposta enviada com sucesso.');
        window.location.reload();
      }
    });
  });
});

const notificationPermissionButton = document.getElementById('notification-permission');
if (notificationPermissionButton) {
  notificationPermissionButton.addEventListener('click', async () => {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
      showNotification('Notificações habilitadas.');
    } else {
      showNotification('Notificações não habilitadas.');
    }
  });
}
