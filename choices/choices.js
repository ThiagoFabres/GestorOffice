// Script universal para aplicar Choices.js em todos os selects
(function() {
    'use strict';
    
    // Configuração padrão
    const defaultConfig = {
        searchEnabled: true,
        searchPlaceholderValue: 'Digite para buscar...',
        noResultsText: 'Nenhum resultado encontrado',
        noChoicesText: 'Nenhuma opção disponível',
        itemSelectText: '',
        shouldSort: false,
        removeItemButton: false,
        placeholder: true,
    };

    
    
    // Armazena todas as instâncias
    window.choicesInstances = [];
    
    // Função para inicializar Choices em todos os selects
    function initAllSelects() {
        // Busca todos os selects que ainda não foram inicializados
        const selects = document.querySelectorAll('select:not(.choices__input)');
        
        selects.forEach(select => {
            // Pula se já foi inicializado
            if (select.getAttribute('data-manual-init') === 'true') {
                return; // Pula este select
            }
            if (select.classList.contains('choices-initialized')) return;
            if (select.id === 'titulo-filtro' || 
                select.id === 'subtitulo-filtro' || 
                select.id === 'titulo' || 
                select.id === 'subtitulo') {
                return; // Pula este select
    }
            // Pula selects dentro de elementos ocultos (para performance)
            if (select.offsetParent === null && !isInModal(select)) return;
            
            try {
                const choices = new Choices(select, defaultConfig);
                window.choicesInstances.push({
                    element: select,
                    instance: choices
                });
                
                // Marca como inicializado
                select.classList.add('choices-initialized');
                
            } catch (error) {
                console.warn('Erro ao inicializar Choices em select:', select, error);
            }
        });
    }
    
    // Verifica se o elemento está dentro de um modal
    function isInModal(element) {
        return element.closest('.modal') !== null;
    }
    
    // Inicializa quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllSelects);
    } else {
        initAllSelects();
    }
    
    // Reinicializa quando modais são abertos
    document.addEventListener('shown.bs.modal', function(e) {
        initAllSelects
    });
    
    // Observa mudanças no DOM para novos selects dinâmicos
    const observer = new MutationObserver(function(mutations) {
        let hasNewSelects = false;
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.tagName === 'SELECT' || node.querySelector('select')) {
                            hasNewSelects = true;
                        }
                    }
                });
            }
        });
        if (hasNewSelects) {
            initAllSelects
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Função auxiliar para obter instância do Choices de um select
    window.getChoicesInstance = function(selector) {
        const element = typeof selector === 'string' 
            ? document.querySelector(selector) 
            : selector;
        
        const instance = window.choicesInstances.find(item => item.element === element);
        return instance ? instance.instance : null;
    };
    
    // Função para definir valor
    window.setChoicesValue = function(selector, value) {
        const instance = window.getChoicesInstance(selector);
        if (instance) {
            instance.setChoiceByValue(value);
        }
    };
    
    // Função para limpar seleção
    window.clearChoices = function(selector) {
        const instance = window.getChoicesInstance(selector);
        if (instance) {
            instance.removeActiveItems();
        }
    };
    
    // Função para adicionar opções dinamicamente
    window.addChoicesOptions = function(selector, options) {
        const instance = window.getChoicesInstance(selector);
        if (instance) {
            instance.setChoices(options, 'value', 'label', false);
        }
    };
    
    // Função para limpar e recarregar opções
    window.reloadChoicesOptions = function(selector, options) {
        const instance = window.getChoicesInstance(selector);
        if (instance) {
            instance.clearStore();
            instance.setChoices(options, 'value', 'label', true);
        }
    };


function filtroSubtitulo(resetSubtitulo = false) {
    var tituloId = document.getElementById('titulo-filtro').value;
    var subtituloSelect = document.getElementById('subtitulo-filtro');
    var options = subtituloSelect.querySelectorAll('option');

    options.forEach(function(option) {
        if (option.value === "") {
            option.style.display = '';
            return;
        }
        if (option.getAttribute('data-titulo-id') === tituloId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });

    if (resetSubtitulo) {
        subtituloSelect.value = ""; // Só reseta se for troca de título
    }
}
    
    // Event listeners para os filtros específicos
    
        const tituloFiltro = document.getElementById('titulo-filtro');
        if (tituloFiltro) {
            tituloFiltro.addEventListener('change', function() {
                filtroSubtitulo(true);
            });
            filtroSubtitulo(false);
        }
        
        const tituloModal = document.getElementById('titulo');
        const subtituloModal = document.getElementById('subtitulo');
        if (tituloModal && subtituloModal) {
            tituloModal.addEventListener('change', function() {
                const tituloId = this.value;
                const instance = window.getChoicesInstance(subtituloModal);
                
                if (!instance) return;
                
                const allOptions = Array.from(subtituloModal.querySelectorAll('option'));
                const filteredChoices = allOptions
                    .filter(option => {
                        if (option.value === "") return true;
                        return option.getAttribute('data-titulo-id') === tituloId;
                    })
                    .map(option => ({
                        value: option.value,
                        label: option.textContent
                    }));
                
                instance.clearStore();
                instance.setChoices(filteredChoices, 'value', 'label', true);
                instance.setChoiceByValue('');
            });
        }
    
    
})();