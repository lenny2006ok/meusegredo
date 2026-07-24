const categories = [
  { key: 'relacionamentos', label: '❤️ Relacionamentos', icon: '❤️' },
  { key: 'trabalho', label: '💼 Trabalho', icon: '💼' },
  { key: 'familia', label: '👨‍👩‍👧 Família', icon: '👨‍👩‍👧' },
  { key: 'escola', label: '🎓 Escola', icon: '🎓' },
  { key: 'dinheiro', label: '💰 Dinheiro', icon: '💰' },
  { key: 'sobrenatural', label: '👻 Sobrenatural', icon: '👻' },
  { key: 'desabafos', label: '😢 Desabafos', icon: '😢' },
  { key: 'segredos', label: '🤫 Segredos', icon: '🤫' },
  { key: 'engracados', label: '🤣 Engraçados', icon: '🤣' }
];

const offensiveWords = [
  'palavrão1', 'palavrão2', 'xixi', 'xuxa', 'idiota', 'burro'
];

function randomPseudo(list) {
  return list[Math.floor(Math.random() * list.length)];
}

function offensiveFilter(text) {
  if (!text) return false;
  const lower = text.toLowerCase();
  return offensiveWords.some(word => lower.includes(word));
}

module.exports = {
  categories,
  randomPseudo,
  offensiveFilter,
};
