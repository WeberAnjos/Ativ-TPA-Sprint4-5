<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento; 
use App\Models\Pergunta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PerguntaController extends Controller
{
    // Habilita o método $this->authorize() usado na Sprint 5
    use AuthorizesRequests;

    public function store(Request $request, Evento $evento)
    {
        // Ticket #007: Validação que impede o envio de perguntas em branco
        $request->validate([
            'texto' => 'required|string|max:1000',
        ], [
            'texto.required' => 'O campo de texto da pergunta é obrigatório.'
        ]);

        $pergunta = new Pergunta();
        $pergunta->texto = $request->texto;
        $pergunta->evento_id = $evento->id;
        $pergunta->user_id = auth()->id(); 
        
        // Ajuste necessário: Defina como true para aparecer no mural imediatamente para os testes,
        // ou altere o EventoController@show para remover o filtro ->where('is_public', true)
        $pergunta->is_public = true;      
        $pergunta->save();

        return redirect()->route('eventos.show', $evento->id)
            ->with('success', 'Sua pergunta foi enviada com sucesso!');
    }

    public function destroy($id)
{
    // Busca a pergunta no banco usando o ID recebido da rota
    $pergunta = Pergunta::findOrFail($id);

    // Validação de segurança manual (Dono da pergunta OU dono do evento)
    if (auth()->id() != $pergunta->user_id && auth()->id() != $pergunta->evento->user_id) {
        abort(403, 'This action is unauthorized.');
    }

    // Se passou na validação, exclui do banco
    $pergunta->delete();

    return redirect()->back()->with('success', 'Pergunta excluída com sucesso!');
}
}