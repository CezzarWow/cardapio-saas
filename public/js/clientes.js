let currentType = 'PF'; // Estado global

// --- MÁSCARAS ---
const masks = {
    phone: (v) => {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
        v = v.replace(/(\d)(\d{4})$/, "$1-$2");
        return v.substring(0, 15);
    },
    cpf: (v) => {
        v = v.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        return v.substring(0, 14);
    },
    cnpj: (v) => {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        return v.substring(0, 18);
    },
    zip: (v) => {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{5})(\d)/, "$1-$2");
        return v.substring(0, 9);
    },
    currency: (v) => {
        v = v.replace(/\D/g, ""); // Remove tudo que não é dígito
        if (v === "") return "";
        v = (parseInt(v) / 100).toFixed(2) + ""; // Divide por 100 e fixa 2 casas
        v = v.replace(".", ","); // Troca ponto por vírgula
        v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1."); // Milhares
        return "R$ " + v;
    }
};

// Event Listeners para Máscaras
document.addEventListener('DOMContentLoaded', () => {
    const addMask = (id, fn) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', e => e.target.value = fn(e.target.value));
    };

    addMask('cli_phone', masks.phone);
    addMask('cli_zip', masks.zip);

    // CPF/CNPJ Dinâmico
    const docInput = document.getElementById('cli_doc');
    if (docInput) {
        docInput.addEventListener('input', e => {
            e.target.value = currentType === 'PF' ? masks.cpf(e.target.value) : masks.cnpj(e.target.value);
        });
    }

    // Moeda (Crediário)
    const limitInput = document.getElementById('cli_limit');
    if (limitInput) {
        limitInput.addEventListener('input', e => {
            let val = e.target.value;
            e.target.value = masks.currency(val);
        });
    }

    // Dia Vencimento (1-31)
    const dueInput = document.getElementById('cli_due');
    if (dueInput) {
        dueInput.addEventListener('input', e => {
            let val = parseInt(e.target.value);
            if (val > 31) e.target.value = 31;
            if (val < 1) e.target.value = '';
        });
    }
});

// 1. Abrir Modal
function openNewClientModal(type) {
    const modal = document.getElementById('superClientModal');
    if (modal) {
        modal.style.display = 'flex';
        // Sem switchTab pois agora é tudo visível (Grid)
        setTypeVisual(type);
        document.getElementById('cli_name').focus();
    }
}

// 2. Configura Visual (Labels)
function setTypeVisual(type) {
    currentType = type;
    const lblName = document.getElementById('lbl-name');
    const lblDoc = document.getElementById('lbl-doc');
    const subtitle = document.getElementById('modal-subtitle');
    const headerDados = document.getElementById('header-dados');

    if (type === 'PF') {
        lblName.innerHTML = 'Nome Completo <span style="color: #ef4444">*</span>';
        lblDoc.innerHTML = 'CPF (Opcional)';
        if (subtitle) subtitle.innerText = 'Preencha os dados do cliente';
        if (headerDados) headerDados.innerText = 'DADOS PESSOAIS';
    } else {
        lblName.innerHTML = 'Razão Social <span style="color: #ef4444">*</span>';
        lblDoc.innerHTML = 'CNPJ (Opcional)';
        if (subtitle) subtitle.innerText = 'Preencha os dados da empresa';
        if (headerDados) headerDados.innerText = 'DADOS DA EMPRESA';
    }

    // Limpa campos dependentes do tipo
    document.getElementById('cli_doc').value = '';
}

// 3. Salvar
// 3. Salvar
function saveSuperClient() {
// Remove formatação de moeda para salvar
    let limitVal = document.getElementById('cli_limit').value;
    limitVal = limitVal.replace('R$ ', '').replace(/\./g, '').replace(',', '.');

    const payload = {
        type: currentType,
        name: document.getElementById('cli_name').value,
        document: document.getElementById('cli_doc').value,
        phone: document.getElementById('cli_phone').value,
        zip_code: document.getElementById('cli_zip').value,
        neighborhood: document.getElementById('cli_bairro').value,
        address: document.getElementById('cli_addr').value,
        address_number: document.getElementById('cli_num').value,
        city: document.getElementById('cli_city').value,
        credit_limit: parseFloat(limitVal) || 0, // Salva float limpo
        due_day: document.getElementById('cli_due').value
    };

    if (!payload.name) return alert('Por favor, preencha o Nome/Razão Social.');

    // Caminho absoluto para garantir que ache a rota
    fetch('/cardapio-saas/public/admin/loja/clientes/salvar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert((currentType === 'PF' ? 'Cliente' : 'Empresa') + ' cadastrado com sucesso!');
                document.getElementById('superClientModal').style.display = 'none';
                window.location.reload();
            } else {
                alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de Conexão: Verifique se o servidor está rodando e o caminho está correto.\nDetalhes no Console.');
        });
}

