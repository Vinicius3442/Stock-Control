// ==========================================
// 1. ANIMAÇÕES DA PÁGINA (Pesquisa, Contadores, Filtros)
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    // --- Animação dos Contadores Numéricos ---
    const counters = document.querySelectorAll('.counter');
    const speed = 50; // Velocidade da contagem

    counters.forEach(counter => {
        const animate = () => {
            const value = +counter.getAttribute('data-target');
            const data = +counter.innerText;
            const time = value / speed;

            if (data < value) {
                counter.innerText = Math.ceil(data + time);
                setTimeout(animate, 20);
            } else {
                counter.innerText = value;
                counter.classList.add('pop'); // Dá o efeito roxo no final
            }
        }
        animate();
    });

    // --- Animação e Lógica da Barra de Pesquisa ---
    const inputPesquisa = document.getElementById('input-pesquisa');
    const iconLupa = document.getElementById('icon-lupa');
    const iconSpinner = document.getElementById('icon-spinner');
    const emptyState = document.getElementById('empty-state');
    let timerPesquisa;

    if (inputPesquisa) {
        inputPesquisa.addEventListener('input', () => {
            iconLupa.style.display = 'none';
            iconSpinner.style.display = 'block';

            clearTimeout(timerPesquisa);

            timerPesquisa = setTimeout(() => {
                iconSpinner.style.display = 'none';
                iconLupa.style.display = 'block';

                const termo = inputPesquisa.value.toLowerCase();
                const cartoes = document.querySelectorAll('.product-card');
                let visiveis = 0;

                cartoes.forEach(cartao => {
                    const titulo = cartao.querySelector('h4').textContent.toLowerCase();
                    const desc = cartao.querySelector('.product-details p').textContent.toLowerCase();

                    if (titulo.includes(termo) || desc.includes(termo)) {
                        visiveis++;
                        cartao.style.display = 'flex';
                        setTimeout(() => {
                            cartao.style.opacity = '1';
                            cartao.style.transform = 'scale(1)';
                        }, 10);
                    } else {
                        cartao.style.opacity = '0';
                        cartao.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            if (cartao.style.opacity === '0') cartao.style.display = 'none';
                        }, 300);
                    }
                });

                // Mostra ou esconde a Carinha Triste animada
                if (emptyState) {
                    if (visiveis === 0) {
                        setTimeout(() => { emptyState.style.display = 'block'; }, 300);
                    } else {
                        emptyState.style.display = 'none';
                    }
                }
            }, 600); // 600ms de animação de carregamento
        });
    }

    // --- Botão "Somente ativos" ---
    const btnFiltro = document.querySelector('.toggle-active');
    if (btnFiltro) {
        btnFiltro.addEventListener('click', function () {
            this.classList.toggle('ativo');
            if (this.classList.contains('ativo')) {
                this.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                this.style.borderColor = '#10B981';
                this.style.color = '#10B981';
            } else {
                this.style.backgroundColor = '#181820';
                this.style.borderColor = '#29292E';
                this.style.color = '#A8A8B3';
            }
        });
    }
});


// ==========================================
// 2. LÓGICA DOS MODAIS (Global)
// ==========================================
let produtoAtualId = null;
let produtoAtualNome = '';
let estoqueAtualMovimentacao = 0;

function fecharModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Modal: Excluir Produto
function abrirModalExcluir(id, nome) {
    document.getElementById('modal-excluir-produto-nome').textContent = nome;
    document.getElementById('input-excluir-produto-id').value = id;
    document.getElementById('modal-excluir-produto').style.display = 'flex';
}

// Modal: Comentários
function abrirModalComentarios(id, nome) {
    produtoAtualId = id;
    produtoAtualNome = nome;

    document.getElementById('modal-produto-nome').textContent = nome + ' / Observações do produto';
    const commentList = document.getElementById('lista-comentarios-container');

    commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Carregando...</p>';
    document.getElementById('modal-lista-comentarios').style.display = 'flex';

    fetch(`buscar-comentarios.php?produto_id=${id}`)
        .then(response => response.json())
        .then(data => {
            commentList.innerHTML = '';

            if (data.length === 0) {
                commentList.innerHTML = '<p style="color: #A8A8B3; text-align: center; padding: 20px;">Nenhum comentário registrado ainda.</p>';
                return;
            }

            data.forEach(com => {
                const iniciais = com.nome.substring(0, 2).toUpperCase();
                const dataComentario = new Date(com.criado_em);
                const formatada = dataComentario.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }) + ' • ' + dataComentario.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                let btnExcluir = '';
                if (com.pode_excluir) {
                    btnExcluir = `<button onclick="excluirComentario(${com.id})" style="background:none; border:none; color: #EF4444; font-size: 0.75rem; cursor: pointer; margin-top: 8px;">Deletar comentário</button>`;
                }

                commentList.innerHTML += `
                    <div class="comment-card">
                        <div class="avatar" style="color: ${com.nivel_acesso === 'admin' ? '#9D7AFF' : '#FFF'};">${iniciais}</div>
                        <div class="comment-body">
                            <h4>${com.nome} <span style="color: #A8A8B3; font-weight: normal;">- ${com.nivel_acesso.charAt(0).toUpperCase() + com.nivel_acesso.slice(1)}</span></h4>
                            <span style="font-size: 0.75rem; color: #52525B;">${formatada}</span>
                            <p style="margin-top: 8px; font-size: 0.875rem; line-height: 1.4; color: #E1E1E6;">${com.comentario}</p>
                            ${btnExcluir}
                        </div>
                    </div>
                `;
            });
        });
}

function abrirModalAddComentario() {
    fecharModal('modal-lista-comentarios'); 
    document.getElementById('modal-add-produto-nome').textContent = produtoAtualNome + ' / Observações do produto';
    document.getElementById('input-add-produto-id').value = produtoAtualId;
    document.getElementById('modal-add-comentario').style.display = 'flex';
}

function excluirComentario(idComentario) {
    if (confirm('Tem certeza que deseja excluir este comentário?')) {
        fetch(`excluir-comentario.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${idComentario}`
        }).then(() => {
            abrirModalComentarios(produtoAtualId, produtoAtualNome);
        });
    }
}

// Modal: Movimentação (Entrada/Saída)
function abrirModalMovimentar(id, nome, estoqueAtual) {
    estoqueAtualMovimentacao = parseInt(estoqueAtual);

    document.getElementById('input-mov-produto-id').value = id;
    document.getElementById('modal-mov-subtitle').textContent = `${nome} / ${estoqueAtual} unidades disponíveis`;
    document.getElementById('input-mov-quantidade').value = '';
    document.getElementById('preview-estoque-final').textContent = `${estoqueAtual} unidades`;

    setTipoMovimentacao('entrada');
    document.getElementById('modal-movimentar').style.display = 'flex';
}

function setTipoMovimentacao(tipo) {
    document.getElementById('input-mov-tipo').value = tipo;

    const btnEntrada = document.getElementById('btn-tab-entrada');
    const btnSaida = document.getElementById('btn-tab-saida');
    const btnSubmit = document.getElementById('btn-submit-movimentacao');

    if (tipo === 'entrada') {
        btnEntrada.classList.add('active');
        btnSaida.classList.remove('active');
        btnSubmit.textContent = 'Registrar entrada';
    } else {
        btnSaida.classList.add('active');
        btnEntrada.classList.remove('active');
        btnSubmit.textContent = 'Registrar saída';
    }
    calcularEstoque();
}

function calcularEstoque() {
    const inputQtd = document.getElementById('input-mov-quantidade');
    const tipo = document.getElementById('input-mov-tipo').value;
    const preview = document.getElementById('preview-estoque-final');

    let qtd = parseInt(inputQtd.value);
    if (isNaN(qtd)) qtd = 0;

    let estoqueFinal = estoqueAtualMovimentacao;

    if (tipo === 'entrada') {
        estoqueFinal += qtd;
    } else {
        estoqueFinal -= qtd;
        if (estoqueFinal < 0) estoqueFinal = 0;
    }
    preview.textContent = `${estoqueFinal} unidades`;
}