    <div class="content">
        
        <div class="box_search advanced">
            <h3>{$texts.MULTIPLE_INDEX_SEARCH}</h3>
            
            <form method="get" action="{$smarty.server.PHP_SELF}" name="advanced_search">
                <input type="hidden" name="lang" value="{$lang}"/>
                <div id="avanced_search_container">
                    <table border="0" cellspacing="0">
                        <tr class="formulario_cabecalho">
                            <td colspan="2">{$texts.SEARCH_SUBMIT}</td>
                            <td>{$texts.IN_INDEX}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input type="text" value="" name="q0" size="50"/>
                            </td>
                            <td>
                                <select name="field0" id="field0">
                                    {foreach from=$colectionData->index_list->index item=availableIndex name=idx}
                                        {assign var=indexKey value=$availableIndex->name|upper}
                                        {assign var=indexPrefix value=$availableIndex->prefix}
                                        
                                        {if $indexKey neq ''}
                                            {if $smarty.foreach.idx.index == 1}
                                                <option value="{$indexPrefix}" selected="1">{$texts.INDEXES.$indexKey}</option>
                                            {else}
                                                <option value="{$indexPrefix}">{$texts.INDEXES.$indexKey}</option>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select name="boolean1" id="boolean1">
                                    <option value="AND" selected="1">AND</option>
                                    <option value="OR">OR</option>
                                    <option value="AND NOT">AND NOT</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" value="" name="q1" size="41"/>
                            </td>
                            <td>
                                <select name="field1" id="field1">
                                    {foreach from=$colectionData->index_list->index item=availableIndex name="idx"}
                                        {assign var=indexKey value=$availableIndex->name|upper}
                                        {assign var=indexPrefix value=$availableIndex->prefix}
                                        
                                        {if $indexKey neq ''}
                                            {if $smarty.foreach.idx.index == 2}
                                                <option value="{$indexPrefix}" selected="1">{$texts.INDEXES.$indexKey}</option>
                                            {else}
                                                <option value="{$indexPrefix}">{$texts.INDEXES.$indexKey}</option>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select name="boolean2" id="boolean2">
                                    <option value="AND" selected="1">AND</option>
                                    <option value="OR">OR</option>
                                    <option value="AND NOT">AND NOT</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" value="" name="q2" size="41"/>
                            </td>
                            <td>
                                <select name="field2" id="field2">
                                    {foreach from=$colectionData->index_list->index item=availableIndex name="idx"}
                                        {assign var=indexKey value=$availableIndex->name|upper}
                                        {assign var=indexPrefix value=$availableIndex->prefix}
                                        
                                        {if $indexKey neq ''}
                                            {if $smarty.foreach.idx.index == 3}
                                                <option value="{$indexPrefix}" selected="1">{$texts.INDEXES.$indexKey}</option>
                                            {else}
                                                <option value="{$indexPrefix}">{$texts.INDEXES.$indexKey}</option>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                    </table>
                    <br/>
                    <input type="submit" value="{$texts.SEARCH_SUBMIT}" name="search" id="buscar"/>
            
                    <h3>{$texts.DETAILED_SEARCH}</h3>
                    
                    <textarea cols="90" rows="6" name="q"></textarea>
                        
                    <br/>
                    <input type="submit" value="{$texts.SEARCH_SUBMIT}" name="search" id="buscar"/>

                    <h3>{$texts.SEARCH_HISTORY}</h3>

                    {if $search_history}

                        <table width="100%">
                            <tr>
                                <td>{$texts.HISTORY_ID}</td>
                                <td>{$texts.HISTORY_QUERY}</td>
                                <td>{$texts.HISTORY_RESULT}</td>
                             </tr>
                            {foreach from=$search_history item=search name=history}
                                {assign var="search_parts" value="|"|explode:$search}
                                {assign var="history_number"  value=$smarty.foreach.history.total-$smarty.foreach.history.index}
                                
                                {if $smarty.foreach.history.index is odd}
                                    <tr class="odd">
                                {else}
                                    <tr>
                                {/if}
                                
                                    <td>
                                        <a href="#" onclick="chooseOperatorToSearch('#{$history_number}',event);">
                                            #{$history_number}
                                        </a>                            
                                    </td>
                                    <td>
                                        {$search_parts[0]}
                                        {if $search_parts[1] neq ''}
                                            <b><i>Filter: </i></b>{$search_parts[1]}
                                        {/if}    
                                    </td>
                                    
                                    <td>
                                        <a href='./index.php?lang={$lang}&q={$search_parts[0]}&filter={$search_parts[1]}&history={$smarty.foreach.history.index+1}'>{$search_parts[2]}</a>
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                        <div id="searchHistoryOperators" class="popup" style="display:none">
                            <h4>
                                <span>{$texts.OPERATORS}</span><!-- TODO trans -->
                                <img src="image/common/close.gif" alt="Close" onclick="showhideLayers('searchHistoryOperators')"/>
                            </h4>
                            <ul>
                                <li onclick="addTermToSearch(this.innerHTML);">OR</li>
                                <li onclick="addTermToSearch(this.innerHTML);">AND</li>
                                <li onclick="addTermToSearch(this.innerHTML);">AND NOT</li>
                            </ul>
                        </div>
                        <input type="submit" value="{$texts.CLEAR_HISTORY}" name="clear_history" id="clear" onclick="if ( confirm('{$texts.CONFIRM_CLEAR_HISTORY}')) return true; else return false; "/>
                  {/if}
                </form>
    </div>
