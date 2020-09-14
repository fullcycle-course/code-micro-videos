import {MUIDataTableColumn} from "mui-datatables";
import {Chip} from "@material-ui/core";
import React from "react";


const types = new Map();
types.set(1, 'Diretor');
types.set(2, 'Ator');

export const columnDefinition: MUIDataTableColumn[] = [
    {
        name: 'name',
        label: 'Nome'
    },
    {
        name: 'type',
        label: 'Tipo',
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return types.get(value);
            }
        }
    },
    {
        name: 'created_at',
        label: 'Criado em',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{new Intl.DateTimeFormat('pt-br').format(new Date(value))}</span>
            }
        }
    }
]